<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

/**
 * Captures a starting reading on a {@see MonotonicClock} and exposes the {@see Elapsed} interval
 * accumulated since.
 *
 * <p>The clock is injected (no default implementation) so callers control the time source.
 * The starting reading is captured at {@see Stopwatch::start} and never mutates. Calling
 * {@see Stopwatch::elapsed} multiple times returns successive measurements from the same
 * starting point.</p>
 */
final readonly class Stopwatch
{
    private function __construct(private MonotonicClock $clock, private int $startedAt)
    {
    }

    /**
     * Creates a Stopwatch by capturing the current reading of the given monotonic clock.
     *
     * @param MonotonicClock $clock The monotonic clock used to measure intervals.
     * @return Stopwatch The created Stopwatch.
     */
    public static function start(MonotonicClock $clock): Stopwatch
    {
        return new Stopwatch(clock: $clock, startedAt: $clock->nanoseconds());
    }

    /**
     * Returns the Elapsed interval accumulated between the captured starting reading and now.
     *
     * @return Elapsed The elapsed interval since {@see Stopwatch::start}.
     */
    public function elapsed(): Elapsed
    {
        return Elapsed::fromNanoseconds(nanoseconds: ($this->clock->nanoseconds() - $this->startedAt));
    }
}
