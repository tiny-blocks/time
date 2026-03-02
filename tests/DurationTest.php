<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Internal\Exceptions\InvalidDuration;

final class DurationTest extends TestCase
{
    public function testZeroCreatesZeroDuration(): void
    {
        /** @Given a zero Duration */
        $duration = Duration::zero();

        /** @Then the seconds should be zero */
        self::assertSame(0, $duration->seconds);
        self::assertTrue($duration->isZero());
    }

    public function testOfSecondsCreatesCorrectDuration(): void
    {
        /** @Given a Duration of 1800 seconds */
        $duration = Duration::ofSeconds(seconds: 1800);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->seconds);
        self::assertFalse($duration->isZero());
    }

    public function testOfSecondsWithZero(): void
    {
        /** @Given a Duration of zero seconds */
        $duration = Duration::ofSeconds(seconds: 0);

        /** @Then it should be zero */
        self::assertSame(0, $duration->seconds);
        self::assertTrue($duration->isZero());
    }

    public function testOfSecondsThrowsWhenNegative(): void
    {
        /** @Then an InvalidDuration exception should be thrown */
        $this->expectException(InvalidDuration::class);

        /** @When creating a Duration with negative seconds */
        Duration::ofSeconds(seconds: -1);
    }

    public function testOfMinutesConvertsToSeconds(): void
    {
        /** @Given a Duration of 30 minutes */
        $duration = Duration::ofMinutes(minutes: 30);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->seconds);
    }

    public function testOfMinutesWithZero(): void
    {
        /** @Given a Duration of zero minutes */
        $duration = Duration::ofMinutes(minutes: 0);

        /** @Then it should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testOfMinutesThrowsWhenNegative(): void
    {
        /** @Then an InvalidDuration exception should be thrown */
        $this->expectException(InvalidDuration::class);

        /** @When creating a Duration with negative minutes */
        Duration::ofMinutes(minutes: -5);
    }

    public function testOfHoursConvertsToSeconds(): void
    {
        /** @Given a Duration of 2 hours */
        $duration = Duration::ofHours(hours: 2);

        /** @Then it should hold 7200 seconds */
        self::assertSame(7200, $duration->seconds);
    }

    public function testOfHoursThrowsWhenNegative(): void
    {
        /** @Then an InvalidDuration exception should be thrown */
        $this->expectException(InvalidDuration::class);

        /** @When creating a Duration with negative hours */
        Duration::ofHours(hours: -1);
    }

    public function testOfDaysConvertsToSeconds(): void
    {
        /** @Given a Duration of 1 day */
        $duration = Duration::ofDays(days: 1);

        /** @Then it should hold 86400 seconds */
        self::assertSame(86400, $duration->seconds);
    }

    public function testOfDaysThrowsWhenNegative(): void
    {
        /** @Then an InvalidDuration exception should be thrown */
        $this->expectException(InvalidDuration::class);

        /** @When creating a Duration with negative days */
        Duration::ofDays(days: -1);
    }

    public function testPlusAddsTwoDurations(): void
    {
        /** @Given a Duration of 30 minutes and another of 15 minutes */
        $thirtyMinutes = Duration::ofMinutes(minutes: 30);
        $fifteenMinutes = Duration::ofMinutes(minutes: 15);

        /** @When adding them */
        $result = $thirtyMinutes->plus(other: $fifteenMinutes);

        /** @Then the result should be 45 minutes in seconds */
        self::assertSame(2700, $result->seconds);
    }

    public function testPlusWithZeroReturnsSameValue(): void
    {
        /** @Given a Duration of 1 hour */
        $oneHour = Duration::ofHours(hours: 1);

        /** @When adding zero */
        $result = $oneHour->plus(other: Duration::zero());

        /** @Then the result should be unchanged */
        self::assertSame(3600, $result->seconds);
    }

    public function testMinusSubtractsTwoDurations(): void
    {
        /** @Given a Duration of 60 minutes and another of 15 minutes */
        $sixtyMinutes = Duration::ofMinutes(minutes: 60);
        $fifteenMinutes = Duration::ofMinutes(minutes: 15);

        /** @When subtracting */
        $result = $sixtyMinutes->minus(other: $fifteenMinutes);

        /** @Then the result should be 45 minutes in seconds */
        self::assertSame(2700, $result->seconds);
    }

    public function testMinusToZero(): void
    {
        /** @Given a Duration of 30 minutes */
        $thirtyMinutes = Duration::ofMinutes(minutes: 30);

        /** @When subtracting itself */
        $result = $thirtyMinutes->minus(other: $thirtyMinutes);

        /** @Then the result should be zero */
        self::assertTrue($result->isZero());
    }

    public function testMinusThrowsWhenResultIsNegative(): void
    {
        /** @Given a smaller Duration subtracting a larger one */
        $tenMinutes = Duration::ofMinutes(minutes: 10);
        $thirtyMinutes = Duration::ofMinutes(minutes: 30);

        /** @Then an InvalidDuration exception should be thrown */
        $this->expectException(InvalidDuration::class);

        /** @When subtracting */
        $tenMinutes->minus(other: $thirtyMinutes);
    }

    public function testIsGreaterThanReturnsTrueWhenLonger(): void
    {
        /** @Given a Duration of 2 hours and another of 30 minutes */
        $twoHours = Duration::ofHours(hours: 2);
        $thirtyMinutes = Duration::ofMinutes(minutes: 30);

        /** @Then the longer should be greater than the shorter */
        self::assertTrue($twoHours->isGreaterThan(other: $thirtyMinutes));
        self::assertFalse($thirtyMinutes->isGreaterThan(other: $twoHours));
    }

    public function testIsGreaterThanReturnsFalseWhenEqual(): void
    {
        /** @Given two equal Durations of 30 minutes */
        $firstThirtyMinutes = Duration::ofMinutes(minutes: 30);
        $secondThirtyMinutes = Duration::ofMinutes(minutes: 30);

        /** @Then neither should be greater than the other */
        self::assertFalse($firstThirtyMinutes->isGreaterThan(other: $secondThirtyMinutes));
    }

    public function testIsLessThanReturnsTrueWhenShorter(): void
    {
        /** @Given a Duration of 15 minutes and another of 1 hour */
        $fifteenMinutes = Duration::ofMinutes(minutes: 15);
        $oneHour = Duration::ofHours(hours: 1);

        /** @Then the shorter should be less than the longer */
        self::assertTrue($fifteenMinutes->isLessThan(other: $oneHour));
        self::assertFalse($oneHour->isLessThan(other: $fifteenMinutes));
    }

    public function testIsLessThanReturnsFalseWhenEqual(): void
    {
        /** @Given two equal Durations of 1 hour */
        $firstHour = Duration::ofHours(hours: 1);
        $secondHour = Duration::ofHours(hours: 1);

        /** @Then neither should be less than the other */
        self::assertFalse($firstHour->isLessThan(other: $secondHour));
    }

    public function testToMinutes(): void
    {
        /** @Given a Duration of 5400 seconds (90 minutes) */
        $ninetyMinutesInSeconds = Duration::ofSeconds(seconds: 5400);

        /** @Then toMinutes should return 90 */
        self::assertSame(90, $ninetyMinutesInSeconds->toMinutes());
    }

    public function testToMinutesTruncates(): void
    {
        /** @Given a Duration of 100 seconds (1 minute and 40 seconds) */
        $hundredSeconds = Duration::ofSeconds(seconds: 100);

        /** @Then toMinutes should return 1 (truncated) */
        self::assertSame(1, $hundredSeconds->toMinutes());
    }

    public function testToHours(): void
    {
        /** @Given a Duration of 2 hours */
        $twoHours = Duration::ofHours(hours: 2);

        /** @Then toHours should return 2 */
        self::assertSame(2, $twoHours->toHours());
    }

    public function testToHoursTruncates(): void
    {
        /** @Given a Duration of 5400 seconds (1 hour and 30 minutes) */
        $ninetyMinutesInSeconds = Duration::ofSeconds(seconds: 5400);

        /** @Then toHours should return 1 (truncated) */
        self::assertSame(1, $ninetyMinutesInSeconds->toHours());
    }

    public function testToDays(): void
    {
        /** @Given a Duration of 3 days */
        $threeDays = Duration::ofDays(days: 3);

        /** @Then toDays should return 3 */
        self::assertSame(3, $threeDays->toDays());
    }

    public function testToDaysTruncates(): void
    {
        /** @Given a Duration of 36 hours (1.5 days) */
        $thirtySixHours = Duration::ofHours(hours: 36);

        /** @Then toDays should return 1 (truncated) */
        self::assertSame(1, $thirtySixHours->toDays());
    }

    public function testPlusAndMinusAreInverse(): void
    {
        /** @Given a Duration of 45 minutes and an addend of 15 minutes */
        $fortyFiveMinutes = Duration::ofMinutes(minutes: 45);
        $fifteenMinutes = Duration::ofMinutes(minutes: 15);

        /** @When adding and then subtracting the same amount */
        $result = $fortyFiveMinutes->plus(other: $fifteenMinutes)->minus(other: $fifteenMinutes);

        /** @Then the result should equal the original */
        self::assertSame($fortyFiveMinutes->seconds, $result->seconds);
    }

    public function testDifferentFactoriesProduceSameResult(): void
    {
        /** @Given a Duration created from each factory for the same amount of time (1 day) */
        $fromSeconds = Duration::ofSeconds(seconds: 86400);
        $fromMinutes = Duration::ofMinutes(minutes: 1440);
        $fromHours = Duration::ofHours(hours: 24);
        $fromDays = Duration::ofDays(days: 1);

        /** @Then all should hold the same number of seconds */
        self::assertSame($fromSeconds->seconds, $fromMinutes->seconds);
        self::assertSame($fromMinutes->seconds, $fromHours->seconds);
        self::assertSame($fromHours->seconds, $fromDays->seconds);
    }

    public function testOfHoursWithZero(): void
    {
        /** @Given a Duration of zero hours */
        $duration = Duration::ofHours(hours: 0);

        /** @Then it should be zero */
        self::assertSame(0, $duration->seconds);
        self::assertTrue($duration->isZero());
    }

    public function testOfDaysWithZero(): void
    {
        /** @Given a Duration of zero days */
        $duration = Duration::ofDays(days: 0);

        /** @Then it should be zero */
        self::assertSame(0, $duration->seconds);
        self::assertTrue($duration->isZero());
    }
}
