<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

/**
 * Represents a day of the week following ISO 8601, where Monday is 1 and Sunday is 7.
 */
enum DayOfWeek: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    private const int DAYS_IN_WEEK = 7;

    /**
     * Derives the day of the week from an Instant.
     *
     * @param Instant $instant The point in time to extract the day from.
     * @return DayOfWeek The corresponding day of the week in UTC.
     */
    public static function fromInstant(Instant $instant): DayOfWeek
    {
        $isoDay = (int)$instant->toDateTimeImmutable()->format('N');

        return self::from($isoDay);
    }

    /**
     * Checks whether this day falls on a weekday (Monday through Friday).
     *
     * @return bool True if this is a weekday.
     */
    public function isWeekday(): bool
    {
        return $this->value <= 5;
    }

    /**
     * Checks whether this day falls on a weekend (Saturday or Sunday).
     *
     * @return bool True if this is a weekend day.
     */
    public function isWeekend(): bool
    {
        return $this->value >= 6;
    }

    /**
     * Returns the forward distance in days from this day to another day of the week.
     * The distance is always in the range [0, 6], measured forward through the week.
     *
     * For example:
     * - Monday->distanceTo(Wednesday) returns 2
     * - Friday->distanceTo(Monday) returns 3 (forward through Sat, Sun, Mon)
     * - Monday->distanceTo(Monday) returns 0
     *
     * @param DayOfWeek $other The target day of the week.
     * @return int The number of days forward from this day to the other (0–6).
     */
    public function distanceTo(DayOfWeek $other): int
    {
        return ($other->value - $this->value + self::DAYS_IN_WEEK) % self::DAYS_IN_WEEK;
    }
}
