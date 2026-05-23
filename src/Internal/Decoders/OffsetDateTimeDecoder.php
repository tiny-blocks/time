<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Decoders;

use DateTimeImmutable;
use DateTimeZone;

final readonly class OffsetDateTimeDecoder implements Decoder
{
    private const string FORMAT = 'Y-m-d\TH:i:sP';
    private const string FORMAT_MICRO = 'Y-m-d\TH:i:s.uP';
    private const string PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+\-]\d{2}:\d{2}$/';
    private const string PATTERN_MICRO = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+[+\-]\d{2}:\d{2}$/';

    public function decode(string $value): ?DateTimeImmutable
    {
        $utc = new DateTimeZone(timezone: 'UTC');

        if (preg_match(self::PATTERN, $value) === 1) {
            $parsed = DateTimeImmutable::createFromFormat(self::FORMAT, $value);

            if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
                return null;
            }

            return $parsed->setTimezone($utc);
        }

        if (preg_match(self::PATTERN_MICRO, $value) === 1) {
            $parsed = DateTimeImmutable::createFromFormat(self::FORMAT_MICRO, $value);

            if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
                return null;
            }

            return $parsed->setTimezone($utc);
        }

        return null;
    }
}
