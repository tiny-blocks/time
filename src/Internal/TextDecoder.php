<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\Exceptions\InvalidInstant;
use TinyBlocks\Time\Internal\Decoders\DatabaseDateTimeDecoder;
use TinyBlocks\Time\Internal\Decoders\OffsetDateTimeDecoder;

final readonly class TextDecoder
{
    private function __construct(private array $decoders)
    {
    }

    public static function create(): TextDecoder
    {
        return new TextDecoder(decoders: [
            new OffsetDateTimeDecoder(),
            new DatabaseDateTimeDecoder()
        ]);
    }

    public function decode(string $value): DateTimeImmutable
    {
        foreach ($this->decoders as $decoder) {
            $decoded = $decoder->decode(value: $value);

            if ($decoded !== null) {
                return $decoded;
            }
        }

        throw InvalidInstant::becauseValueCannotBeDecoded(value: $value);
    }
}
