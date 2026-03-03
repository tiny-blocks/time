<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Internal\Exceptions\InvalidSeconds;

final class DurationTest extends TestCase
{
    public function testDurationZeroCreatesZeroDuration(): void
    {
        /** @Given a zero Duration */
        $duration = Duration::zero();

        /** @Then the seconds should be zero */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testDurationFromSecondsCreatesCorrectDuration(): void
    {
        /** @Given a Duration created from 1800 seconds */
        $duration = Duration::fromSeconds(seconds: 1800);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->toSeconds());

        /** @And it should not be zero */
        self::assertFalse($duration->isZero());
    }

    public function testDurationFromSecondsWithZero(): void
    {
        /** @Given a Duration created from zero seconds */
        $duration = Duration::fromSeconds(seconds: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testDurationWhenNegativeSeconds(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative seconds */
        Duration::fromSeconds(seconds: -1);
    }

    public function testDurationFromMinutesConvertsToSeconds(): void
    {
        /** @Given a Duration created from 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->toSeconds());
    }

    public function testDurationFromMinutesWithZero(): void
    {
        /** @Given a Duration created from zero minutes */
        $duration = Duration::fromMinutes(minutes: 0);

        /** @Then it should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testDurationWhenNegativeMinutes(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative minutes */
        Duration::fromMinutes(minutes: -5);
    }

    public function testDurationFromHoursConvertsToSeconds(): void
    {
        /** @Given a Duration created from 2 hours */
        $duration = Duration::fromHours(hours: 2);

        /** @Then it should hold 7200 seconds */
        self::assertSame(7200, $duration->toSeconds());
    }

    public function testDurationFromHoursWithZero(): void
    {
        /** @Given a Duration created from zero hours */
        $duration = Duration::fromHours(hours: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testDurationWhenNegativeHours(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative hours */
        Duration::fromHours(hours: -1);
    }

    public function testDurationFromDaysConvertsToSeconds(): void
    {
        /** @Given a Duration created from 1 day */
        $duration = Duration::fromDays(days: 1);

        /** @Then it should hold 86400 seconds */
        self::assertSame(86400, $duration->toSeconds());
    }

    public function testDurationFromDaysWithZero(): void
    {
        /** @Given a Duration created from zero days */
        $duration = Duration::fromDays(days: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testDurationWhenNegativeDays(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative days */
        Duration::fromDays(days: -1);
    }

    public function testDurationPlusAddsTwoDurations(): void
    {
        /** @Given a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @And a Duration of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @When adding them */
        $result = $thirtyMinutes->plus(other: $fifteenMinutes);

        /** @Then the result should be 2700 seconds (45 minutes) */
        self::assertSame(2700, $result->toSeconds());
    }

    public function testDurationPlusWithZeroReturnsSameValue(): void
    {
        /** @Given a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @When adding zero */
        $result = $oneHour->plus(other: Duration::zero());

        /** @Then the result should be unchanged */
        self::assertSame(3600, $result->toSeconds());
    }

    public function testDurationMinusSubtractsTwoDurations(): void
    {
        /** @Given a Duration of 60 minutes */
        $sixtyMinutes = Duration::fromMinutes(minutes: 60);

        /** @And a Duration of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @When subtracting */
        $result = $sixtyMinutes->minus(other: $fifteenMinutes);

        /** @Then the result should be 2700 seconds (45 minutes) */
        self::assertSame(2700, $result->toSeconds());
    }

    public function testDurationMinusToZero(): void
    {
        /** @Given a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When subtracting itself */
        $result = $thirtyMinutes->minus(other: $thirtyMinutes);

        /** @Then the result should be zero */
        self::assertTrue($result->isZero());
    }

    public function testDurationMinusWhenResultIsNegative(): void
    {
        /** @Given a Duration of 10 minutes */
        $tenMinutes = Duration::fromMinutes(minutes: 10);

        /** @And a larger Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @Then an exception indicating that subtraction would result in a negative value should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When subtracting the larger from the smaller */
        $tenMinutes->minus(other: $thirtyMinutes);
    }

    public function testDurationDivideReturnsWholeCount(): void
    {
        /** @Given a Duration of 90 minutes */
        $ninetyMinutes = Duration::fromMinutes(minutes: 90);

        /** @And a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When dividing */
        $result = $ninetyMinutes->divide(other: $thirtyMinutes);

        /** @Then the result should be 3 */
        self::assertSame(3, $result);
    }

    public function testDurationDivideTruncatesRemainder(): void
    {
        /** @Given a Duration of 100 seconds */
        $hundredSeconds = Duration::fromSeconds(seconds: 100);

        /** @And a Duration of 30 seconds */
        $thirtySeconds = Duration::fromSeconds(seconds: 30);

        /** @When dividing */
        $result = $hundredSeconds->divide(other: $thirtySeconds);

        /** @Then the result should be 3 (truncated from 3.33) */
        self::assertSame(3, $result);
    }

    public function testDurationDivideByItselfReturnsOne(): void
    {
        /** @Given a Duration of 45 minutes */
        $fortyFiveMinutes = Duration::fromMinutes(minutes: 45);

        /** @When dividing by itself */
        $result = $fortyFiveMinutes->divide(other: $fortyFiveMinutes);

        /** @Then the result should be 1 */
        self::assertSame(1, $result);
    }

    public function testDurationDivideByLargerReturnsZero(): void
    {
        /** @Given a Duration of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @And a larger Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @When dividing the smaller by the larger */
        $result = $fifteenMinutes->divide(other: $oneHour);

        /** @Then the result should be 0 */
        self::assertSame(0, $result);
    }

    public function testDurationDivideWithExactMultiple(): void
    {
        /** @Given a Duration of 2 hours */
        $twoHours = Duration::fromHours(hours: 2);

        /** @And a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When dividing */
        $result = $twoHours->divide(other: $thirtyMinutes);

        /** @Then the result should be 4 */
        self::assertSame(4, $result);
    }

    public function testDurationDivideWhenDivisorIsZero(): void
    {
        /** @Given a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @Then an exception indicating that seconds cannot be divided by zero should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When dividing by zero */
        $oneHour->divide(other: Duration::zero());
    }

    public function testDurationDivideWithZeroDurationFromSecondsWhenDivisorIsZero(): void
    {
        /** @Given a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @And a Duration of zero seconds */
        $zeroDuration = Duration::fromSeconds(seconds: 0);

        /** @Then an exception indicating that seconds cannot be divided by zero should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When dividing by the zero Duration */
        $thirtyMinutes->divide(other: $zeroDuration);
    }

    public function testDurationIsGreaterThanReturnsTrueWhenLonger(): void
    {
        /** @Given a Duration of 2 hours */
        $twoHours = Duration::fromHours(hours: 2);

        /** @And a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @Then the longer should be greater than the shorter */
        self::assertTrue($twoHours->isGreaterThan(other: $thirtyMinutes));

        /** @And the shorter should not be greater than the longer */
        self::assertFalse($thirtyMinutes->isGreaterThan(other: $twoHours));
    }

    public function testDurationIsGreaterThanReturnsFalseWhenEqual(): void
    {
        /** @Given a Duration of 30 minutes */
        $firstThirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @And another Duration of 30 minutes */
        $secondThirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @Then neither should be greater than the other */
        self::assertFalse($firstThirtyMinutes->isGreaterThan(other: $secondThirtyMinutes));
    }

    public function testDurationIsLessThanReturnsTrueWhenShorter(): void
    {
        /** @Given a Duration of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @And a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @Then the shorter should be less than the longer */
        self::assertTrue($fifteenMinutes->isLessThan(other: $oneHour));

        /** @And the longer should not be less than the shorter */
        self::assertFalse($oneHour->isLessThan(other: $fifteenMinutes));
    }

    public function testDurationIsLessThanReturnsFalseWhenEqual(): void
    {
        /** @Given a Duration of 1 hour */
        $firstHour = Duration::fromHours(hours: 1);

        /** @And another Duration of 1 hour */
        $secondHour = Duration::fromHours(hours: 1);

        /** @Then neither should be less than the other */
        self::assertFalse($firstHour->isLessThan(other: $secondHour));
    }

    public function testDurationToSeconds(): void
    {
        /** @Given a Duration created from 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @Then toSeconds should return 1800 */
        self::assertSame(1800, $duration->toSeconds());
    }

    public function testDurationToMinutes(): void
    {
        /** @Given a Duration created from 5400 seconds */
        $duration = Duration::fromSeconds(seconds: 5400);

        /** @Then toMinutes should return 90 */
        self::assertSame(90, $duration->toMinutes());
    }

    public function testDurationToMinutesTruncates(): void
    {
        /** @Given a Duration created from 100 seconds */
        $duration = Duration::fromSeconds(seconds: 100);

        /** @Then toMinutes should return 1 (truncated from 1.67) */
        self::assertSame(1, $duration->toMinutes());
    }

    public function testDurationToHours(): void
    {
        /** @Given a Duration created from 2 hours */
        $duration = Duration::fromHours(hours: 2);

        /** @Then toHours should return 2 */
        self::assertSame(2, $duration->toHours());
    }

    public function testDurationToHoursTruncates(): void
    {
        /** @Given a Duration created from 5400 seconds */
        $duration = Duration::fromSeconds(seconds: 5400);

        /** @Then toHours should return 1 (truncated from 1.5) */
        self::assertSame(1, $duration->toHours());
    }

    public function testDurationToDays(): void
    {
        /** @Given a Duration created from 3 days */
        $duration = Duration::fromDays(days: 3);

        /** @Then toDays should return 3 */
        self::assertSame(3, $duration->toDays());
    }

    public function testDurationToDaysTruncates(): void
    {
        /** @Given a Duration created from 36 hours */
        $duration = Duration::fromHours(hours: 36);

        /** @Then toDays should return 1 (truncated from 1.5) */
        self::assertSame(1, $duration->toDays());
    }

    public function testDurationPlusAndMinusAreInverse(): void
    {
        /** @Given a Duration of 45 minutes */
        $fortyFiveMinutes = Duration::fromMinutes(minutes: 45);

        /** @And an addend of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @When adding and then subtracting the same amount */
        $result = $fortyFiveMinutes->plus(other: $fifteenMinutes)->minus(other: $fifteenMinutes);

        /** @Then the result should equal the original */
        self::assertSame($fortyFiveMinutes->toSeconds(), $result->toSeconds());
    }

    public function testDurationDifferentFactoriesProduceSameResult(): void
    {
        /** @Given a Duration of 86400 seconds */
        $fromSeconds = Duration::fromSeconds(seconds: 86400);

        /** @And a Duration of 1440 minutes */
        $fromMinutes = Duration::fromMinutes(minutes: 1440);

        /** @And a Duration of 24 hours */
        $fromHours = Duration::fromHours(hours: 24);

        /** @And a Duration of 1 day */
        $fromDays = Duration::fromDays(days: 1);

        /** @Then all should hold the same number of seconds */
        self::assertSame($fromSeconds->toSeconds(), $fromMinutes->toSeconds());
        self::assertSame($fromMinutes->toSeconds(), $fromHours->toSeconds());
        self::assertSame($fromHours->toSeconds(), $fromDays->toSeconds());
    }

    public function testDurationDivideIsConsistentWithSlotExpansion(): void
    {
        /** @Given a Duration of 90 minutes */
        $appointmentDuration = Duration::fromMinutes(minutes: 90);

        /** @And a slot size of 30 minutes */
        $slotSize = Duration::fromMinutes(minutes: 30);

        /** @When dividing the appointment by the slot size */
        $slotCount = $appointmentDuration->divide(other: $slotSize);

        /** @Then the slot count should be 3 */
        self::assertSame(3, $slotCount);

        /** @And reconstructing from slot count should match the original duration */
        $reconstructed = Duration::fromMinutes(minutes: $slotCount * $slotSize->toMinutes());

        self::assertSame($appointmentDuration->toSeconds(), $reconstructed->toSeconds());
    }
}
