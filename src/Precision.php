<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

/**
 * Granularity level for ISO 8601 datetime string emission.
 */
enum Precision: string
{
    case Seconds = 'seconds';
    case Milliseconds = 'milliseconds';
    case Microseconds = 'microseconds';
}
