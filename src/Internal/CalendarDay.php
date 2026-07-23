<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

final class CalendarDay
{
    private function __construct()
    {
    }

    public static function clamp(int $year, int $month, int $day): int
    {
        $isLeapYear = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400) === 0;

        return match ($month) {
            2           => min($day, $isLeapYear ? 29 : 28),
            4, 6, 9, 11 => min($day, 30),
            default     => $day
        };
    }
}
