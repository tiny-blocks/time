<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Exceptions;

use InvalidArgumentException;
use TinyBlocks\Time\Instant;

final class InvalidPeriod extends InvalidArgumentException
{
    public static function becauseStartIsNotBeforeEnd(Instant $from, Instant $to): InvalidPeriod
    {
        $template = 'Period start <%s> must be strictly before end <%s>.';

        return new InvalidPeriod(message: sprintf($template, $from->toIso8601(), $to->toIso8601()));
    }

    public static function becauseDurationIsZero(): InvalidPeriod
    {
        return new InvalidPeriod(message: 'Period duration must not be zero.');
    }
}
