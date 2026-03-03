<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use TinyBlocks\Time\Internal\Exceptions\InvalidSeconds;
use TinyBlocks\Time\Internal\Seconds;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents an immutable, unsigned quantity of time measured in seconds.
 * A Duration has no reference point on the timeline, it expresses only "how much" time.
 */
final readonly class Duration implements ValueObject
{
    use ValueObjectBehavior;

    private const int SECONDS_PER_MINUTE = 60;
    private const int SECONDS_PER_HOUR = 3600;
    private const int SECONDS_PER_DAY = 86400;

    private function __construct(private Seconds $seconds)
    {
    }

    /**
     * Creates a Duration of zero length.
     *
     * @return Duration A zero-length Duration.
     */
    public static function zero(): Duration
    {
        return new Duration(seconds: Seconds::zero());
    }

    /**
     * Creates a Duration from a number of seconds.
     *
     * @param int $seconds The number of seconds (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidSeconds If the value is negative.
     */
    public static function fromSeconds(int $seconds): Duration
    {
        return new Duration(seconds: Seconds::from(value: $seconds));
    }

    /**
     * Creates a Duration from a number of minutes.
     *
     * @param int $minutes The number of minutes (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidSeconds If the value is negative.
     */
    public static function fromMinutes(int $minutes): Duration
    {
        return new Duration(seconds: Seconds::from(value: $minutes * self::SECONDS_PER_MINUTE));
    }

    /**
     * Creates a Duration from a number of hours.
     *
     * @param int $hours The number of hours (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidSeconds If the value is negative.
     */
    public static function fromHours(int $hours): Duration
    {
        return new Duration(seconds: Seconds::from(value: $hours * self::SECONDS_PER_HOUR));
    }

    /**
     * Creates a Duration from a number of days.
     *
     * @param int $days The number of days (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidSeconds If the value is negative.
     */
    public static function fromDays(int $days): Duration
    {
        return new Duration(seconds: Seconds::from(value: $days * self::SECONDS_PER_DAY));
    }

    /**
     * Returns a new Duration by adding another Duration to this one.
     *
     * @param Duration $other The Duration to add.
     * @return Duration A new Duration representing the sum.
     */
    public function plus(Duration $other): Duration
    {
        return new Duration(seconds: $this->seconds->plus(other: $other->seconds));
    }

    /**
     * Returns a new Duration by subtracting another Duration from this one.
     *
     * @param Duration $other The Duration to subtract.
     * @return Duration A new Duration representing the difference.
     * @throws InvalidSeconds If the result was negative.
     */
    public function minus(Duration $other): Duration
    {
        return new Duration(seconds: $this->seconds->minus(other: $other->seconds));
    }

    /**
     * Returns the number of times the other Duration fits wholly into this one.
     * The result is truncated toward zero.
     *
     * @param Duration $other The divisor Duration.
     * @return int The number of whole times the other fits into this Duration.
     * @throws InvalidSeconds If the divisor is zero.
     */
    public function divide(Duration $other): int
    {
        return $this->seconds->divide(other: $other->seconds);
    }

    /**
     * Returns true if this Duration has zero length.
     *
     * @return bool True if this Duration is zero seconds.
     */
    public function isZero(): bool
    {
        return $this->seconds->isZero();
    }

    /**
     * Returns true if this Duration is strictly greater than another.
     *
     * @param Duration $other The Duration to compare against.
     * @return bool True if this Duration is longer.
     */
    public function isGreaterThan(Duration $other): bool
    {
        return $this->seconds->isGreaterThan(other: $other->seconds);
    }

    /**
     * Returns true if this Duration is strictly less than another.
     *
     * @param Duration $other The Duration to compare against.
     * @return bool True if this Duration is shorter.
     */
    public function isLessThan(Duration $other): bool
    {
        return $this->seconds->isLessThan(other: $other->seconds);
    }

    /**
     * Returns the total number of seconds in this Duration.
     *
     * @return int The number of seconds.
     */
    public function toSeconds(): int
    {
        return $this->seconds->value;
    }

    /**
     * Returns the total number of whole minutes in this Duration.
     *
     * @return int The number of whole minutes.
     */
    public function toMinutes(): int
    {
        return $this->seconds->divideByScalar(divisor: self::SECONDS_PER_MINUTE);
    }

    /**
     * Returns the total number of whole hours in this Duration.
     *
     * @return int The number of whole hours.
     */
    public function toHours(): int
    {
        return $this->seconds->divideByScalar(divisor: self::SECONDS_PER_HOUR);
    }

    /**
     * Returns the total number of whole days in this Duration.
     *
     * @return int The number of whole days.
     */
    public function toDays(): int
    {
        return $this->seconds->divideByScalar(divisor: self::SECONDS_PER_DAY);
    }
}
