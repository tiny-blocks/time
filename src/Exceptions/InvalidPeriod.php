<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Exceptions;

use InvalidArgumentException;
use TinyBlocks\Time\Instant;

/**
 * Raised when a Period cannot be constructed because its boundaries are not strictly ordered.
 */
final class InvalidPeriod extends InvalidArgumentException
{
    public static function becauseDurationIsZero(): InvalidPeriod
    {
        return new InvalidPeriod(message: 'Period duration must not be zero.');
    }

    public static function becauseStartIsNotBeforeEnd(Instant $from, Instant $to): InvalidPeriod
    {
        $template = 'Period start <%s> must be strictly before end <%s>.';

        return new InvalidPeriod(message: sprintf($template, $from->toIso8601(), $to->toIso8601()));
    }
}
