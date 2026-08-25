<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\Timezone;

final class IsoDate
{
    private const string CANONICAL_FORMAT = 'Y-m-d';

    private function __construct()
    {
    }

    public static function restore(string $canonical): DateTimeImmutable
    {
        return new DateTimeImmutable(datetime: $canonical, timezone: Timezone::utc()->toDateTimeZone());
    }

    public static function canonicalize(DateTimeImmutable $datetime): string
    {
        return $datetime->format(self::CANONICAL_FORMAT);
    }
}
