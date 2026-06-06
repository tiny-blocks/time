<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a string is not a recognized IANA timezone identifier.
 */
final class InvalidTimezone extends InvalidArgumentException
{
    public static function becauseIdentifierIsInvalid(string $identifier): InvalidTimezone
    {
        $template = 'Timezone <%s> is invalid.';

        return new InvalidTimezone(message: sprintf($template, $identifier));
    }
}
