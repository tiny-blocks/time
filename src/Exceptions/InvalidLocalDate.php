<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Exceptions;

use InvalidArgumentException;

final class InvalidLocalDate extends InvalidArgumentException
{
    public static function becauseValueIsInvalid(string $value): InvalidLocalDate
    {
        $template = 'The value <%s> is not a valid local date.';

        return new InvalidLocalDate(message: sprintf($template, $value));
    }

    public static function becauseComponentsAreInvalid(int $year, int $month, int $day): InvalidLocalDate
    {
        $template = 'Year <%d>, month <%d>, and day <%d> do not form a valid calendar date.';

        return new InvalidLocalDate(message: sprintf($template, $year, $month, $day));
    }
}
