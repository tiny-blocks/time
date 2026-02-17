<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Decoders;

use DateTimeImmutable;
use TinyBlocks\Time\Timezone;

final readonly class DatabaseDateTimeDecoder implements Decoder
{
    private const string FORMAT = 'Y-m-d H:i:s';
    private const string FORMAT_MICRO = 'Y-m-d H:i:s.u';

    public function decode(string $value): ?DateTimeImmutable
    {
        $hasMicroseconds = str_contains($value, '.');
        $utc = Timezone::utc()->toDateTimeZone();
        $format = $hasMicroseconds ? self::FORMAT_MICRO : self::FORMAT;
        $parsed = DateTimeImmutable::createFromFormat($format, $value, $utc);

        if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
            return null;
        }

        return $parsed->setTimezone($utc);
    }
}
