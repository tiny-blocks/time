<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use TinyBlocks\Time\Internal\Exceptions\InvalidDuration;
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

    private function __construct(public int $seconds)
    {
    }

    /**
     * Creates a Duration of zero length.
     *
     * @return Duration A zero-length Duration.
     */
    public static function zero(): Duration
    {
        return new Duration(seconds: 0);
    }

    /**
     * Creates a Duration from a number of seconds.
     *
     * @param int $seconds The number of seconds (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidDuration If the value is negative.
     */
    public static function ofSeconds(int $seconds): Duration
    {
        if ($seconds < 0) {
            throw InvalidDuration::becauseIsNegative(value: $seconds, unit: 'seconds');
        }

        return new Duration(seconds: $seconds);
    }

    /**
     * Creates a Duration from a number of minutes.
     *
     * @param int $minutes The number of minutes (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidDuration If the value is negative.
     */
    public static function ofMinutes(int $minutes): Duration
    {
        if ($minutes < 0) {
            throw InvalidDuration::becauseIsNegative(value: $minutes, unit: 'minutes');
        }

        return new Duration(seconds: $minutes * self::SECONDS_PER_MINUTE);
    }

    /**
     * Creates a Duration from a number of hours.
     *
     * @param int $hours The number of hours (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidDuration If the value is negative.
     */
    public static function ofHours(int $hours): Duration
    {
        if ($hours < 0) {
            throw InvalidDuration::becauseIsNegative(value: $hours, unit: 'hours');
        }

        return new Duration(seconds: $hours * self::SECONDS_PER_HOUR);
    }

    /**
     * Creates a Duration from a number of days.
     *
     * @param int $days The number of days (must be non-negative).
     * @return Duration The created Duration.
     * @throws InvalidDuration If the value is negative.
     */
    public static function ofDays(int $days): Duration
    {
        if ($days < 0) {
            throw InvalidDuration::becauseIsNegative(value: $days, unit: 'days');
        }

        return new Duration(seconds: $days * self::SECONDS_PER_DAY);
    }

    /**
     * Returns a new Duration by adding another Duration to this one.
     *
     * @param Duration $other The Duration to add.
     * @return Duration A new Duration representing the sum.
     */
    public function plus(Duration $other): Duration
    {
        return new Duration(seconds: $this->seconds + $other->seconds);
    }

    /**
     * Returns a new Duration by subtracting another Duration from this one.
     *
     * @param Duration $other The Duration to subtract.
     * @return Duration A new Duration representing the difference.
     * @throws InvalidDuration If the result was negative.
     */
    public function minus(Duration $other): Duration
    {
        $result = $this->seconds - $other->seconds;

        if ($result < 0) {
            throw InvalidDuration::becauseResultIsNegative(current: $this->seconds, subtracted: $other->seconds);
        }

        return new Duration(seconds: $result);
    }

    /**
     * Returns true if this Duration has zero length.
     *
     * @return bool True if this Duration is zero seconds.
     */
    public function isZero(): bool
    {
        return $this->seconds === 0;
    }

    /**
     * Returns true if this Duration is strictly greater than another.
     *
     * @param Duration $other The Duration to compare against.
     * @return bool True if this Duration is longer.
     */
    public function isGreaterThan(Duration $other): bool
    {
        return $this->seconds > $other->seconds;
    }

    /**
     * Returns true if this Duration is strictly less than another.
     *
     * @param Duration $other The Duration to compare against.
     * @return bool True if this Duration is shorter.
     */
    public function isLessThan(Duration $other): bool
    {
        return $this->seconds < $other->seconds;
    }

    /**
     * Returns the total number of whole minutes in this Duration.
     *
     * @return int The number of whole minutes.
     */
    public function toMinutes(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_MINUTE);
    }

    /**
     * Returns the total number of whole hours in this Duration.
     *
     * @return int The number of whole hours.
     */
    public function toHours(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_HOUR);
    }

    /**
     * Returns the total number of whole days in this Duration.
     *
     * @return int The number of whole days.
     */
    public function toDays(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_DAY);
    }
}
