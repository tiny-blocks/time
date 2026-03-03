<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use TinyBlocks\Time\Internal\Exceptions\InvalidPeriod;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a half-open time interval [from, to) between two UTC instants.
 * The start is inclusive and the end is exclusive.
 */
final readonly class Period implements ValueObject
{
    use ValueObjectBehavior;

    private function __construct(public Instant $from, public Instant $to)
    {
    }

    /**
     * Creates a Period from two instants, ensuring the start is strictly before the end.
     *
     * @param Instant $from The inclusive start of the period.
     * @param Instant $to The exclusive end of the period.
     * @return Period The created period.
     * @throws InvalidPeriod If the start is not before the end.
     */
    public static function from(Instant $from, Instant $to): Period
    {
        if ($from->isAfterOrEqual(other: $to)) {
            throw InvalidPeriod::becauseStartIsNotBeforeEnd(from: $from, to: $to);
        }

        return new Period(from: $from, to: $to);
    }

    /**
     * Creates a Period from a start instant and a Duration.
     *
     * @param Instant $from The inclusive start of the period.
     * @param Duration $duration The length of the period (must not be zero).
     * @return Period The created period.
     * @throws InvalidPeriod If the duration is zero.
     */
    public static function startingAt(Instant $from, Duration $duration): Period
    {
        if ($duration->isZero()) {
            throw InvalidPeriod::becauseDurationIsZero();
        }

        return new Period(from: $from, to: $from->plus(duration: $duration));
    }

    /**
     * Returns the Duration of this period.
     *
     * @return Duration The time elapsed from start to end.
     */
    public function duration(): Duration
    {
        return $this->from->durationUntil(other: $this->to);
    }

    /**
     * Checks whether the given instant falls within this period (inclusive start, exclusive end).
     *
     * @param Instant $instant The instant to check.
     * @return bool True if the instant is within [from, to).
     */
    public function contains(Instant $instant): bool
    {
        return $instant->isAfterOrEqual(other: $this->from)
            && $instant->isBefore(other: $this->to);
    }

    /**
     * Checks whether this period overlaps with another period.
     * Two half-open intervals [A, B) and [C, D) overlap when A < D and C < B.
     *
     * @param Period $other The period to check against.
     * @return bool True if the periods share any common time.
     */
    public function overlapsWith(Period $other): bool
    {
        return $this->from->isBefore(other: $other->to)
            && $other->from->isBefore(other: $this->to);
    }
}
