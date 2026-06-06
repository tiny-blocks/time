<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Stopwatch;

final class StopwatchTest extends TestCase
{
    public function testStartWhenClockReadsSameValueTwiceThenElapsedIsZero(): void
    {
        /** @Given a monotonic clock that returns the same reading twice */
        $clock = new MonotonicClockFake(42, 42);

        /** @And a stopwatch started against that clock */
        $stopwatch = Stopwatch::start(clock: $clock);

        /** @When reading the elapsed interval */
        $elapsed = $stopwatch->elapsed();

        /** @Then the elapsed interval is zero */
        self::assertSame(0.0, $elapsed->toMilliseconds());
    }

    public function testElapsedWhenInvokedTwiceThenStartedReadingStaysFixed(): void
    {
        /** @Given a monotonic clock scheduled to return 100, 200, then 300 nanoseconds */
        $clock = new MonotonicClockFake(100, 200, 300);

        /** @And a stopwatch started against that clock */
        $stopwatch = Stopwatch::start(clock: $clock);

        /** @When invoked twice */
        $first = $stopwatch->elapsed();
        $second = $stopwatch->elapsed();

        /** @Then both deltas are measured from the original starting reading */
        self::assertSame(100, $first->nanoseconds);
        self::assertSame(200, $second->nanoseconds);
    }

    public function testStartWhenClockAdvancesThenElapsedEqualsReadingDifference(): void
    {
        /** @Given a monotonic clock scheduled to return 1,000,000 then 2,500,000 nanoseconds */
        $clock = new MonotonicClockFake(1_000_000, 2_500_000);

        /** @And a stopwatch started against that clock */
        $stopwatch = Stopwatch::start(clock: $clock);

        /** @When reading the elapsed interval */
        $elapsed = $stopwatch->elapsed();

        /** @Then the elapsed interval equals the difference between the two readings */
        self::assertSame(1.5, $elapsed->toMilliseconds());
    }
}
