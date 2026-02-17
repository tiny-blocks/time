<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Decoders;

use DateTimeImmutable;
use DateTimeZone;

final readonly class OffsetDateTimeDecoder implements Decoder
{
    private const string FORMAT = 'Y-m-d\TH:i:sP';
    private const string PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+\-]\d{2}:\d{2}$/';

    public function decode(string $value): ?DateTimeImmutable
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat(self::FORMAT, $value);

        if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
            return null;
        }

        return $parsed->setTimezone(new DateTimeZone('UTC'));
    }
}
