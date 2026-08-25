<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\Timezone;

final class IsoMoment
{
    private const string CANONICAL_FORMAT = 'Y-m-d\TH:i:s.uP';

    private function __construct()
    {
    }

    public static function restore(string $canonical): DateTimeImmutable
    {
        $datetime = new DateTimeImmutable(datetime: $canonical);

        return $datetime->setTimezone(Timezone::utc()->toDateTimeZone());
    }

    public static function canonicalize(DateTimeImmutable $datetime): string
    {
        return $datetime->setTimezone(Timezone::utc()->toDateTimeZone())->format(self::CANONICAL_FORMAT);
    }
}
