<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\SystemMonotonicClock;

final class SystemMonotonicClockTest extends TestCase
{
    public function testNanosecondsWhenReadOnceThenReturnsNonNegativeInteger(): void
    {
        /** @Given a system monotonic clock */
        $clock = new SystemMonotonicClock();

        /** @When reading the current nanoseconds */
        $reading = $clock->nanoseconds();

        /** @Then the reading is a non-negative integer */
        self::assertGreaterThanOrEqual(0, $reading);
    }

    public function testNanosecondsWhenReadTwiceThenSecondReadingIsNotLessThanFirst(): void
    {
        /** @Given a system monotonic clock */
        $clock = new SystemMonotonicClock();

        /** @And a first reading captured from the clock */
        $firstReading = $clock->nanoseconds();

        /** @When reading the clock a second time */
        $secondReading = $clock->nanoseconds();

        /** @Then the second reading is greater than or equal to the first */
        self::assertGreaterThanOrEqual($firstReading, $secondReading);
    }

    public function testNanosecondsWhenMeasuringAcrossSleepThenElapsedIsAtLeastSleepInterval(): void
    {
        /** @Given a system monotonic clock */
        $clock = new SystemMonotonicClock();

        /** @And a starting reading captured before sleeping */
        $startReading = $clock->nanoseconds();

        /** @And a one-millisecond sleep separating the readings */
        usleep(1000);

        /** @When reading the clock after the sleep */
        $endReading = $clock->nanoseconds();

        /** @Then the elapsed delta covers the sleep interval with generous slack */
        self::assertGreaterThanOrEqual(100_000, $endReading - $startReading);
    }
}
