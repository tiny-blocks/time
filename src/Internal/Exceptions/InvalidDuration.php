<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Exceptions;

use InvalidArgumentException;

final class InvalidDuration extends InvalidArgumentException
{
    public static function becauseIsNegative(int $value, string $unit): InvalidDuration
    {
        $template = 'Duration in %s must be non-negative, got <%d>.';

        return new InvalidDuration(message: sprintf($template, $unit, $value));
    }

    public static function becauseResultIsNegative(int $current, int $subtracted): InvalidDuration
    {
        $template = 'Duration subtraction would result in a negative value: <%d> - <%d>.';

        return new InvalidDuration(message: sprintf($template, $current, $subtracted));
    }
}
