<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a Seconds value cannot be produced because the result would violate its invariants.
 */
final class InvalidSeconds extends InvalidArgumentException
{
    public static function becauseIsNegative(int $value): InvalidSeconds
    {
        $template = 'Seconds must be non-negative, got <%d>.';

        return new InvalidSeconds(message: sprintf($template, $value));
    }

    public static function becauseDivisorIsZero(): InvalidSeconds
    {
        return new InvalidSeconds(message: 'Seconds cannot be divided by zero.');
    }

    public static function becauseResultIsNegative(int $current, int $subtracted): InvalidSeconds
    {
        $template = 'Seconds subtraction would result in a negative value: <%d> - <%d>.';

        return new InvalidSeconds(message: sprintf($template, $current, $subtracted));
    }
}
