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
}
