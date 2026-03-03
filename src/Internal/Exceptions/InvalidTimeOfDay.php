<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Exceptions;

use InvalidArgumentException;
use TinyBlocks\Time\TimeOfDay;

final class InvalidTimeOfDay extends InvalidArgumentException
{
    public static function becauseHourIsOutOfRange(int $hour): InvalidTimeOfDay
    {
        $template = 'Hour must be between 0 and 23, got <%d>.';

        return new InvalidTimeOfDay(message: sprintf($template, $hour));
    }

    public static function becauseMinuteIsOutOfRange(int $minute): InvalidTimeOfDay
    {
        $template = 'Minute must be between 0 and 59, got <%d>.';

        return new InvalidTimeOfDay(message: sprintf($template, $minute));
    }

    public static function becauseFormatIsInvalid(string $value): InvalidTimeOfDay
    {
        $template = 'Time of day <%s> must be in HH:MM format.';

        return new InvalidTimeOfDay(message: sprintf($template, $value));
    }

    public static function becauseEndIsNotAfterStart(TimeOfDay $from, TimeOfDay $to): InvalidTimeOfDay
    {
        $template = 'End time <%s> must be after start time <%s>.';

        return new InvalidTimeOfDay(message: sprintf($template, $to->toString(), $from->toString()));
    }
}
