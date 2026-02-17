<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Decoders;

use DateTimeImmutable;

interface Decoder
{
    /**
     * Attempts to decode the given string into a DateTimeImmutable instance.
     *
     * @param string $value The string to decode.
     * @return DateTimeImmutable|null The decoded DateTimeImmutable instance, or null if decoding fails.
     */
    public function decode(string $value): ?DateTimeImmutable;
}
