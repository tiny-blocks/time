<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\Internal\Decoders\DatabaseDateTimeDecoder;
use TinyBlocks\Time\Internal\Decoders\Decoder;
use TinyBlocks\Time\Internal\Decoders\OffsetDateTimeDecoder;
use TinyBlocks\Time\Internal\Exceptions\InvalidInstant;

final readonly class TextDecoder
{
    /**
     * @param list<Decoder> $decoders
     */
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
            $result = $decoder->decode(value: $value);

            if ($result !== null) {
                return $result;
            }
        }

        throw new InvalidInstant(value: $value);
    }
}
