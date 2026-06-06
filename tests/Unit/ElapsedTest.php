<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Elapsed;

final class ElapsedTest extends TestCase
{
    public function testEqualsWhenSameNanosecondsThenReturnsTrue(): void
    {
        /** @Given an Elapsed of 1,500,000 nanoseconds */
        $first = Elapsed::fromNanoseconds(nanoseconds: 1_500_000);

        /** @And another Elapsed of the same nanoseconds */
        $second = Elapsed::fromNanoseconds(nanoseconds: 1_500_000);

        /** @When comparing the two Elapsed instances for structural equality */
        $areEqual = $first->equals(other: $second);

        /** @Then the comparison returns true */
        self::assertTrue($areEqual);
    }

    public function testEqualsWhenDifferentNanosecondsThenReturnsFalse(): void
    {
        /** @Given an Elapsed of 1,500,000 nanoseconds */
        $first = Elapsed::fromNanoseconds(nanoseconds: 1_500_000);

        /** @And another Elapsed of a different nanoseconds count */
        $second = Elapsed::fromNanoseconds(nanoseconds: 2_000_000);

        /** @When comparing the two Elapsed instances for structural equality */
        $areEqual = $first->equals(other: $second);

        /** @Then the comparison returns false */
        self::assertFalse($areEqual);
    }

    public function testFromNanosecondsWhenZeroThenConvertsToZeroMilliseconds(): void
    {
        /** @Given an Elapsed of zero nanoseconds */
        $elapsed = Elapsed::fromNanoseconds(nanoseconds: 0);

        /** @When converting the Elapsed to milliseconds */
        $milliseconds = $elapsed->toMilliseconds();

        /** @Then the result is zero milliseconds */
        self::assertSame(0.0, $milliseconds);
    }

    public function testFromNanosecondsWhenConvertedToMillisecondsThenScalesByOneMillion(): void
    {
        /** @Given an Elapsed of 1,500,000 nanoseconds */
        $elapsed = Elapsed::fromNanoseconds(nanoseconds: 1_500_000);

        /** @When converting the Elapsed to milliseconds */
        $milliseconds = $elapsed->toMilliseconds();

        /** @Then the result equals one and a half milliseconds */
        self::assertSame(1.5, $milliseconds);
    }

    public function testFromNanosecondsWhenConvertedToMillisecondsThenRoundsToTwoDecimals(): void
    {
        /** @Given an Elapsed of 1,234,567 nanoseconds */
        $elapsed = Elapsed::fromNanoseconds(nanoseconds: 1_234_567);

        /** @When converting the Elapsed to milliseconds */
        $milliseconds = $elapsed->toMilliseconds();

        /** @Then the result is rounded to two decimal places */
        self::assertSame(1.23, $milliseconds);
    }

    public function testFromNanosecondsWhenConvertedToMillisecondsThenRoundsHalfAwayFromZero(): void
    {
        /** @Given an Elapsed of 1,235,000 nanoseconds */
        $elapsed = Elapsed::fromNanoseconds(nanoseconds: 1_235_000);

        /** @When converting the Elapsed to milliseconds */
        $milliseconds = $elapsed->toMilliseconds();

        /** @Then the second decimal rounds up */
        self::assertSame(1.24, $milliseconds);
    }
}
