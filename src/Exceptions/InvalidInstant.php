<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a string value cannot be decoded into a valid Instant.
 */
final class InvalidInstant extends InvalidArgumentException
{
    public static function becauseValueCannotBeDecoded(string $value): InvalidInstant
    {
        $template = 'The value <%s> could not be decoded into a valid instant.';

        return new InvalidInstant(message: sprintf($template, $value));
    }
}
