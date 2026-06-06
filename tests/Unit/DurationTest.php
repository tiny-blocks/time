<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Exceptions\InvalidSeconds;

final class DurationTest extends TestCase
{
    public function testFromMinutesWhenZeroThenIsZero(): void
    {
        /** @When creating a Duration from zero minutes */
        $duration = Duration::fromMinutes(minutes: 0);

        /** @Then it should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testZeroThenHoldsNoSecondsAndIsZero(): void
    {
        /** @When creating a zero Duration */
        $duration = Duration::zero();

        /** @Then the seconds should be zero */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testToSecondsThenReturnsTotalSeconds(): void
    {
        /** @Given a Duration of 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @When converting to seconds */
        $result = $duration->toSeconds();

        /** @Then it should return 1800 */
        self::assertSame(1800, $result);
    }

    public function testToDaysWhenHasRemainderThenTruncates(): void
    {
        /** @Given a Duration of 36 hours */
        $duration = Duration::fromHours(hours: 36);

        /** @When converting to days */
        $result = $duration->toDays();

        /** @Then it should return 1 (truncated from 1.5) */
        self::assertSame(1, $result);
    }

    public function testFromDaysWhenZeroThenHoldsZeroSeconds(): void
    {
        /** @When creating a Duration from zero days */
        $duration = Duration::fromDays(days: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testToHoursWhenHasRemainderThenTruncates(): void
    {
        /** @Given a Duration of 5400 seconds */
        $duration = Duration::fromSeconds(seconds: 5400);

        /** @When converting to hours */
        $result = $duration->toHours();

        /** @Then it should return 1 (truncated from 1.5) */
        self::assertSame(1, $result);
    }

    public function testFromHoursWhenZeroThenHoldsZeroSeconds(): void
    {
        /** @When creating a Duration from zero hours */
        $duration = Duration::fromHours(hours: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testToDaysWhenExactMultipleThenReturnsDays(): void
    {
        /** @Given a Duration of 3 days */
        $duration = Duration::fromDays(days: 3);

        /** @When converting to days */
        $result = $duration->toDays();

        /** @Then it should return 3 */
        self::assertSame(3, $result);
    }

    public function testToMinutesWhenHasRemainderThenTruncates(): void
    {
        /** @Given a Duration of 100 seconds */
        $duration = Duration::fromSeconds(seconds: 100);

        /** @When converting to minutes */
        $result = $duration->toMinutes();

        /** @Then it should return 1 (truncated from 1.67) */
        self::assertSame(1, $result);
    }

    public function testFromSecondsWhenZeroThenHoldsZeroSeconds(): void
    {
        /** @When creating a Duration from zero seconds */
        $duration = Duration::fromSeconds(seconds: 0);

        /** @Then it should hold zero seconds */
        self::assertSame(0, $duration->toSeconds());

        /** @And it should be identified as zero */
        self::assertTrue($duration->isZero());
    }

    public function testPlusWhenOtherIsZeroThenReturnsSameValue(): void
    {
        /** @Given a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @When adding zero */
        $result = $oneHour->plus(other: Duration::zero());

        /** @Then the result should be unchanged */
        self::assertSame(3600, $result->toSeconds());
    }

    public function testDivideWhenDividingByItselfThenReturnsOne(): void
    {
        /** @Given a Duration of 45 minutes */
        $fortyFiveMinutes = Duration::fromMinutes(minutes: 45);

        /** @When dividing by itself */
        $result = $fortyFiveMinutes->divide(other: $fortyFiveMinutes);

        /** @Then the result should be 1 */
        self::assertSame(1, $result);
    }

    public function testDivideWhenDivisorIsLargerThenReturnsZero(): void
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

    public function testToHoursWhenExactMultipleThenReturnsHours(): void
    {
        /** @Given a Duration of 2 hours */
        $duration = Duration::fromHours(hours: 2);

        /** @When converting to hours */
        $result = $duration->toHours();

        /** @Then it should return 2 */
        self::assertSame(2, $result);
    }

    public function testMinusWhenSubtractingItselfThenReturnsZero(): void
    {
        /** @Given a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When subtracting itself */
        $result = $thirtyMinutes->minus(other: $thirtyMinutes);

        /** @Then the result should be zero */
        self::assertTrue($result->isZero());
    }

    public function testPlusWhenAddingTwoDurationsThenSumsSeconds(): void
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

    public function testDivideWhenDivisorHasRemainderThenTruncates(): void
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

    public function testFromDaysWhenNegativeThenThrowsInvalidSeconds(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative days */
        Duration::fromDays(days: -1);
    }

    public function testToMinutesWhenExactMultipleThenReturnsMinutes(): void
    {
        /** @Given a Duration of 5400 seconds */
        $duration = Duration::fromSeconds(seconds: 5400);

        /** @When converting to minutes */
        $result = $duration->toMinutes();

        /** @Then it should return 90 */
        self::assertSame(90, $result);
    }

    public function testFromHoursWhenNegativeThenThrowsInvalidSeconds(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative hours */
        Duration::fromHours(hours: -1);
    }

    public function testIsLessThanWhenSubjectIsShorterThenReturnsTrue(): void
    {
        /** @Given a Duration of 15 minutes */
        $fifteenMinutes = Duration::fromMinutes(minutes: 15);

        /** @And a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @When comparing the shorter to the longer */
        $result = $fifteenMinutes->isLessThan(other: $oneHour);

        /** @Then the shorter should be less than the longer */
        self::assertTrue($result);

        /** @And the longer should not be less than the shorter */
        self::assertFalse($oneHour->isLessThan(other: $fifteenMinutes));
    }

    public function testFromDaysWhenPositiveValueThenConvertsToSeconds(): void
    {
        /** @When creating a Duration from 1 day */
        $duration = Duration::fromDays(days: 1);

        /** @Then it should hold 86400 seconds */
        self::assertSame(86400, $duration->toSeconds());
    }

    public function testDivideWhenDivisorIsZeroThenThrowsInvalidSeconds(): void
    {
        /** @Given a Duration of 1 hour */
        $oneHour = Duration::fromHours(hours: 1);

        /** @Then an exception indicating that seconds cannot be divided by zero should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When dividing by zero */
        $oneHour->divide(other: Duration::zero());
    }

    public function testFromHoursWhenPositiveValueThenConvertsToSeconds(): void
    {
        /** @When creating a Duration from 2 hours */
        $duration = Duration::fromHours(hours: 2);

        /** @Then it should hold 7200 seconds */
        self::assertSame(7200, $duration->toSeconds());
    }

    public function testFromMinutesWhenNegativeThenThrowsInvalidSeconds(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative minutes */
        Duration::fromMinutes(minutes: -5);
    }

    public function testFromSecondsWhenNegativeThenThrowsInvalidSeconds(): void
    {
        /** @Then an exception indicating that seconds must be non-negative should be thrown */
        $this->expectException(InvalidSeconds::class);

        /** @When creating a Duration with negative seconds */
        Duration::fromSeconds(seconds: -1);
    }

    public function testIsGreaterThanWhenSubjectIsLongerThenReturnsTrue(): void
    {
        /** @Given a Duration of 2 hours */
        $twoHours = Duration::fromHours(hours: 2);

        /** @And a Duration of 30 minutes */
        $thirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When comparing the longer to the shorter */
        $result = $twoHours->isGreaterThan(other: $thirtyMinutes);

        /** @Then the longer should be greater than the shorter */
        self::assertTrue($result);

        /** @And the shorter should not be greater than the longer */
        self::assertFalse($thirtyMinutes->isGreaterThan(other: $twoHours));
    }

    public function testIsLessThanWhenDurationsAreEqualThenReturnsFalse(): void
    {
        /** @Given a Duration of 1 hour */
        $firstHour = Duration::fromHours(hours: 1);

        /** @And another Duration of 1 hour */
        $secondHour = Duration::fromHours(hours: 1);

        /** @When comparing equal Durations */
        $result = $firstHour->isLessThan(other: $secondHour);

        /** @Then neither should be less than the other */
        self::assertFalse($result);
    }

    public function testDivideWhenDivisorIsExactMultipleThenReturnsCount(): void
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

    public function testMinusWhenSubtractingSmallerThenReturnsDifference(): void
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

    public function testDivideWhenDivisorFitsExactlyThenReturnsWholeCount(): void
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

    public function testFromMinutesWhenPositiveValueThenConvertsToSeconds(): void
    {
        /** @When creating a Duration from 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->toSeconds());
    }

    public function testIsGreaterThanWhenDurationsAreEqualThenReturnsFalse(): void
    {
        /** @Given a Duration of 30 minutes */
        $firstThirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @And another Duration of 30 minutes */
        $secondThirtyMinutes = Duration::fromMinutes(minutes: 30);

        /** @When comparing equal Durations */
        $result = $firstThirtyMinutes->isGreaterThan(other: $secondThirtyMinutes);

        /** @Then neither should be greater than the other */
        self::assertFalse($result);
    }

    public function testDivideWhenAppointmentSplitIntoSlotsThenSlotCountIsExact(): void
    {
        /** @Given a Duration of 90 minutes */
        $appointmentDuration = Duration::fromMinutes(minutes: 90);

        /** @And a slot size of 30 minutes */
        $slotSize = Duration::fromMinutes(minutes: 30);

        /** @When dividing the appointment by the slot size */
        $slotCount = $appointmentDuration->divide(other: $slotSize);

        /** @Then the slot count should be 3 */
        self::assertSame(3, $slotCount);
    }

    public function testFromMinutesWhenReconstructedFromSlotCountThenMatchesOriginal(): void
    {
        /** @Given a Duration of 90 minutes */
        $appointmentDuration = Duration::fromMinutes(minutes: 90);

        /** @And a slot size of 30 minutes */
        $slotSize = Duration::fromMinutes(minutes: 30);

        /** @And the slot count obtained by dividing the appointment by the slot size */
        $slotCount = $appointmentDuration->divide(other: $slotSize);

        /** @When reconstructing a Duration from the slot count and slot size */
        $reconstructed = Duration::fromMinutes(minutes: $slotCount * $slotSize->toMinutes());

        /** @Then it should match the original appointment duration */
        self::assertSame($appointmentDuration->toSeconds(), $reconstructed->toSeconds());
    }

    public function testPlusAndMinusWhenAppliedWithSameAmountThenAreInverse(): void
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

    public function testMinusWhenResultWouldBeNegativeThenThrowsInvalidSeconds(): void
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

    public function testFromSecondsWhenPositiveValueThenHoldsThatNumberOfSeconds(): void
    {
        /** @When creating a Duration from 1800 seconds */
        $duration = Duration::fromSeconds(seconds: 1800);

        /** @Then it should hold 1800 seconds */
        self::assertSame(1800, $duration->toSeconds());

        /** @And it should not be zero */
        self::assertFalse($duration->isZero());
    }

    public function testDivideWhenDivisorIsZeroDurationFromSecondsThenThrowsInvalidSeconds(): void
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

    public function testFactoriesWhenSameDurationExpressedDifferentlyThenProduceSameResult(): void
    {
        /** @Given a Duration of 86400 seconds */
        $fromSeconds = Duration::fromSeconds(seconds: 86400);

        /** @And a Duration of 1440 minutes */
        $fromMinutes = Duration::fromMinutes(minutes: 1440);

        /** @And a Duration of 24 hours */
        $fromHours = Duration::fromHours(hours: 24);

        /** @When creating the same Duration from 1 day */
        $fromDays = Duration::fromDays(days: 1);

        /** @Then all should hold the same number of seconds */
        self::assertSame($fromSeconds->toSeconds(), $fromMinutes->toSeconds());
        self::assertSame($fromMinutes->toSeconds(), $fromHours->toSeconds());
        self::assertSame($fromHours->toSeconds(), $fromDays->toSeconds());
    }
}
