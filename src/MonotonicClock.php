<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

/**
 * High-resolution monotonic clock for measuring elapsed time.
 *
 * <p>Distinct from the wall-clock value objects {@see Instant} and {@see Duration}. A monotonic
 * clock exposes an opaque counter that never moves backward and is unaffected by wall-clock
 * adjustments (NTP corrections, manual changes, leap seconds). Readings are only meaningful as
 * deltas between two calls on the same clock, and absolute values carry no calendar semantics.</p>
 */
interface MonotonicClock
{
    /**
     * Returns the current monotonic reading as an integer nanosecond count.
     *
     * <p>Successive readings are guaranteed to be non-decreasing. The absolute value has no
     * meaning on its own. Subtract two readings to obtain an elapsed interval in nanoseconds.</p>
     *
     * @return int The current monotonic reading in nanoseconds.
     */
    public function nanoseconds(): int;
}
