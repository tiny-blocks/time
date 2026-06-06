<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

/**
 * Default {@see MonotonicClock} backed by PHP's <code>hrtime(true)</code> high-resolution timer.
 */
final readonly class SystemMonotonicClock implements MonotonicClock
{
    public function nanoseconds(): int
    {
        return hrtime(true);
    }
}
