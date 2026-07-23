<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\LocalDate;
use TinyBlocks\Time\Timezone;

final class ZonedShift
{
    private function __construct()
    {
    }

    public static function recompose(
        Timezone $zone,
        DateTimeImmutable $original,
        LocalDate $shiftedDate
    ): DateTimeImmutable {
        $dateTimeZone = $zone->toDateTimeZone();
        $timeOfDay = $original->setTimezone($dateTimeZone)->format('H:i:s.u');
        $template = '%s %s';
        $wallClock = sprintf($template, $shiftedDate->toIso8601(), $timeOfDay);
        $recomposed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $wallClock, $dateTimeZone);

        return $recomposed->setTimezone(Timezone::utc()->toDateTimeZone());
    }
}
