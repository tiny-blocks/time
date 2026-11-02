<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeZone;

final class TimezoneDatabase
{
    private static ?array $identifiers = null;

    private function __construct()
    {
    }

    public static function omits(string $identifier): bool
    {
        TimezoneDatabase::$identifiers ??= array_flip(DateTimeZone::listIdentifiers());

        return !isset(TimezoneDatabase::$identifiers[$identifier]);
    }
}
