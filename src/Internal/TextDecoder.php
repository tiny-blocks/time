<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use DateTimeImmutable;
use TinyBlocks\Time\Internal\Decoders\Decoder;
use TinyBlocks\Time\Internal\Decoders\OffsetDateTimeDecoder;
use TinyBlocks\Time\Internal\Exceptions\InvalidInstant;

final readonly class TextDecoder
{
    /** @var Decoder[] */
    private array $decoders;

    /**
     * @param Decoder[] $decoders
     */
    private function __construct(array $decoders)
    {
        $this->decoders = $decoders;
    }

    public static function create(): self
    {
        return new TextDecoder(decoders: [new OffsetDateTimeDecoder()]);
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
