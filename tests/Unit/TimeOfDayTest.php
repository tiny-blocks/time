<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Exceptions\InvalidTimeOfDay;
use TinyBlocks\Time\TimeOfDay;

final class TimeOfDayTest extends TestCase
{
    public function testToDurationWhenMidnightThenReturnsZero(): void
    {
        /** @Given midnight */
        $time = TimeOfDay::midnight();

        /** @When converting to Duration */
        $duration = $time->toDuration();

        /** @Then the duration should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testNoonThenReturnsTwelveHourAndZeroMinute(): void
    {
        /** @When creating the noon TimeOfDay */
        $time = TimeOfDay::noon();

        /** @Then the hour should be 12 */
        self::assertSame(12, $time->hour);

        /** @And the minute should be 0 */
        self::assertSame(0, $time->minute);

        /** @And the string representation should be '12:00' */
        self::assertSame('12:00', $time->toString());
    }

    public function testFromStringWhenValidThenReturnsTimeOfDay(): void
    {
        /** @When creating a TimeOfDay from the string '08:30' */
        $time = TimeOfDay::fromString(value: '08:30');

        /** @Then the hour should be 8 */
        self::assertSame(8, $time->hour);

        /** @And the minute should be 30 */
        self::assertSame(30, $time->minute);
    }

    public function testIsAfterWhenEqualToOtherThenReturnsFalse(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @When checking if the time is after itself */
        $result = $time->isAfter(other: $same);

        /** @Then isAfter should return false */
        self::assertFalse($result);
    }

    public function testIsAfterWhenLaterThanOtherThenReturnsTrue(): void
    {
        /** @Given a later time */
        $later = TimeOfDay::from(hour: 18, minute: 0);

        /** @And an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @When checking if the later time is after the earlier */
        $result = $later->isAfter(other: $earlier);

        /** @Then the later should be after the earlier */
        self::assertTrue($result);
    }

    public function testIsBeforeWhenEqualToOtherThenReturnsFalse(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @When checking if the time is before itself */
        $result = $time->isBefore(other: $same);

        /** @Then isBefore should return false */
        self::assertFalse($result);
    }

    public function testMidnightThenReturnsZeroHourAndZeroMinute(): void
    {
        /** @When creating the midnight TimeOfDay */
        $time = TimeOfDay::midnight();

        /** @Then the hour should be 0 */
        self::assertSame(0, $time->hour);

        /** @And the minute should be 0 */
        self::assertSame(0, $time->minute);

        /** @And the string representation should be '00:00' */
        self::assertSame('00:00', $time->toString());
    }

    public function testFromWhenHourAndMinuteThenReturnsTimeOfDay(): void
    {
        /** @When creating a TimeOfDay with hour 8 and minute 30 */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @Then the hour should be 8 */
        self::assertSame(8, $time->hour);

        /** @And the minute should be 30 */
        self::assertSame(30, $time->minute);
    }

    public function testIsBeforeWhenLaterThanOtherThenReturnsFalse(): void
    {
        /** @Given a later time */
        $later = TimeOfDay::from(hour: 14, minute: 30);

        /** @And an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @When checking if the later time is before the earlier */
        $result = $later->isBefore(other: $earlier);

        /** @Then the later should not be before the earlier */
        self::assertFalse($result);
    }

    public function testFromStringWhenHasSecondsThenDiscardsSeconds(): void
    {
        /** @When creating a TimeOfDay from the string '08:30:00' */
        $time = TimeOfDay::fromString(value: '08:30:00');

        /** @Then the hour should be 8 */
        self::assertSame(8, $time->hour);

        /** @And the minute should be 30 */
        self::assertSame(30, $time->minute);

        /** @And the string representation should discard seconds */
        self::assertSame('08:30', $time->toString());
    }

    public function testIsBeforeWhenEarlierThanOtherThenReturnsTrue(): void
    {
        /** @Given an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @And a later time */
        $later = TimeOfDay::from(hour: 14, minute: 30);

        /** @When checking if the earlier time is before the later */
        $result = $earlier->isBefore(other: $later);

        /** @Then the earlier should be before the later */
        self::assertTrue($result);
    }

    public function testFromStringWhenEmptyThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing an empty string */
        TimeOfDay::fromString(value: '');
    }

    public function testIsAfterOrEqualWhenEqualToOtherThenReturnsTrue(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @When checking if the time is after or equal to itself */
        $result = $time->isAfterOrEqual(other: $same);

        /** @Then isAfterOrEqual should return true */
        self::assertTrue($result);
    }

    public function testIsBeforeOrEqualWhenEqualToOtherThenReturnsTrue(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @When checking if the time is before or equal to itself */
        $result = $time->isBeforeOrEqual(other: $same);

        /** @Then isBeforeOrEqual should return true */
        self::assertTrue($result);
    }

    public function testFromWhenHourAndMinuteAreZeroThenReturnsMidnight(): void
    {
        /** @When creating a TimeOfDay with hour 0 and minute 0 */
        $time = TimeOfDay::from(hour: 0, minute: 0);

        /** @Then the hour should be 0 */
        self::assertSame(0, $time->hour);

        /** @And the minute should be 0 */
        self::assertSame(0, $time->minute);
    }

    public function testFromInstantWhenAfternoonThenReturnsHourAndMinute(): void
    {
        /** @Given an Instant at 14:30 UTC */
        $instant = Instant::fromString(value: '2026-02-17T14:30:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then the hour should be 14 */
        self::assertSame(14, $time->hour);

        /** @And the minute should be 30 */
        self::assertSame(30, $time->minute);
    }

    public function testFromWhenHourIsNegativeThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with negative hour */
        TimeOfDay::from(hour: -1, minute: 0);
    }

    public function testToMinutesSinceMidnightWhenMidnightThenReturnsZero(): void
    {
        /** @Given midnight */
        $time = TimeOfDay::midnight();

        /** @When converting to minutes since midnight */
        $minutes = $time->toMinutesSinceMidnight();

        /** @Then minutes since midnight should be 0 */
        self::assertSame(0, $minutes);
    }

    public function testFromStringWhenEndOfDayThenReturnsLastHourAndMinute(): void
    {
        /** @When creating a TimeOfDay from the string '23:59' */
        $time = TimeOfDay::fromString(value: '23:59');

        /** @Then the hour should be 23 */
        self::assertSame(23, $time->hour);

        /** @And the minute should be 59 */
        self::assertSame(59, $time->minute);
    }

    public function testFromWhenHourAndMinuteAreMaximumThenReturnsEndOfDay(): void
    {
        /** @When creating a TimeOfDay with hour 23 and minute 59 */
        $time = TimeOfDay::from(hour: 23, minute: 59);

        /** @Then the hour should be 23 */
        self::assertSame(23, $time->hour);

        /** @And the minute should be 59 */
        self::assertSame(59, $time->minute);
    }

    public function testFromWhenMinuteIsNegativeThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with negative minute */
        TimeOfDay::from(hour: 10, minute: -1);
    }

    public function testDurationUntilWhenEndIsAfterStartThenReturnsDuration(): void
    {
        /** @Given a start time at 08:00 */
        $from = TimeOfDay::from(hour: 8, minute: 0);

        /** @And an end time at 12:30 */
        $to = TimeOfDay::from(hour: 12, minute: 30);

        /** @When calculating the duration */
        $duration = $from->durationUntil(other: $to);

        /** @Then the duration should be 270 minutes */
        self::assertSame(270, $duration->toMinutes());
    }

    public function testFromInstantAndFromWhenSameTimeThenProduceSameResult(): void
    {
        /** @Given an Instant at 14:30 UTC */
        $instant = Instant::fromString(value: '2026-02-17T14:30:00+00:00');

        /** @When creating from both methods */
        $fromInstant = TimeOfDay::fromInstant(instant: $instant);

        /** @And creating from hour and minute directly */
        $fromFactory = TimeOfDay::from(hour: 14, minute: 30);

        /** @Then both should produce the same hour */
        self::assertSame($fromInstant->hour, $fromFactory->hour);

        /** @And the same minute */
        self::assertSame($fromInstant->minute, $fromFactory->minute);
    }

    public function testFromInstantWhenEndOfDayThenReturnsLastHourAndMinute(): void
    {
        /** @Given an Instant at 23:59 */
        $instant = Instant::fromString(value: '2026-02-17T23:59:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then the hour should be 23 */
        self::assertSame(23, $time->hour);

        /** @And the minute should be 59 */
        self::assertSame(59, $time->minute);
    }

    public function testFromStringAndToStringWhenRoundTripThenPreservesValue(): void
    {
        /** @Given a time string */
        $original = '14:30';

        /** @When parsing and formatting back */
        $result = TimeOfDay::fromString(value: $original)->toString();

        /** @Then the result should match the original */
        self::assertSame($original, $result);
    }

    public function testFromWhenHourExceedsMaximumThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with hour 24 */
        TimeOfDay::from(hour: 24, minute: 0);
    }

    public function testToDurationWhenEightThirtyThenReturnsMinutesAndSeconds(): void
    {
        /** @Given 08:30 */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @When converting to Duration */
        $duration = $time->toDuration();

        /** @Then the duration should be 510 minutes */
        self::assertSame(510, $duration->toMinutes());

        /** @And 30600 seconds */
        self::assertSame(30600, $duration->toSeconds());
    }

    public function testFromStringWhenMidnightThenReturnsZeroHourAndZeroMinute(): void
    {
        /** @When creating a TimeOfDay from the string '00:00' */
        $time = TimeOfDay::fromString(value: '00:00');

        /** @Then the hour should be 0 */
        self::assertSame(0, $time->hour);

        /** @And the minute should be 0 */
        self::assertSame(0, $time->minute);
    }

    public function testFromWhenMinuteExceedsMaximumThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with minute 60 */
        TimeOfDay::from(hour: 10, minute: 60);
    }

    public function testIsBeforeAndIsAfterWhenComparedThenAreMutuallyExclusive(): void
    {
        /** @Given an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @And a later time */
        $later = TimeOfDay::from(hour: 18, minute: 0);

        /** @When checking if the earlier is before the later */
        $earlierIsBefore = $earlier->isBefore(other: $later);

        /** @Then earlier should be before later */
        self::assertTrue($earlierIsBefore);

        /** @And earlier should not be after later */
        self::assertFalse($earlier->isAfter(other: $later));

        /** @And later should be after earlier */
        self::assertTrue($later->isAfter(other: $earlier));

        /** @And later should not be before earlier */
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testDurationUntilWhenEqualToOtherThenThrowsInvalidTimeOfDay(): void
    {
        /** @Given a time at 10:00 */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time at 10:00 */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @Then an exception indicating that end must be after start should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When calculating the duration */
        $time->durationUntil(other: $same);
    }

    public function testFromInstantWhenMidnightThenReturnsZeroHourAndZeroMinute(): void
    {
        /** @Given an Instant at midnight */
        $instant = Instant::fromString(value: '2026-02-17T00:00:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then the hour should be 0 */
        self::assertSame(0, $time->hour);

        /** @And the minute should be 0 */
        self::assertSame(0, $time->minute);
    }

    public function testFromStringWhenFormatIsInvalidThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing an invalid string */
        TimeOfDay::fromString(value: '8:30');
    }

    public function testFromStringWhenHourIsOutOfRangeThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with hour 25 */
        TimeOfDay::fromString(value: '25:00');
    }

    public function testFromStringWhenMinuteIsOutOfRangeThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with minute 60 */
        TimeOfDay::fromString(value: '10:60');
    }

    public function testDurationUntilWhenEndIsBeforeStartThenThrowsInvalidTimeOfDay(): void
    {
        /** @Given a start time at 14:00 */
        $from = TimeOfDay::from(hour: 14, minute: 0);

        /** @And an end time at 08:00 */
        $to = TimeOfDay::from(hour: 8, minute: 0);

        /** @Then an exception indicating that end must be after start should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When calculating the duration */
        $from->durationUntil(other: $to);
    }

    public function testToMinutesSinceMidnightWhenNoonThenReturnsSevenHundredTwenty(): void
    {
        /** @Given noon */
        $time = TimeOfDay::noon();

        /** @When converting to minutes since midnight */
        $minutes = $time->toMinutesSinceMidnight();

        /** @Then minutes since midnight should be 720 */
        self::assertSame(720, $minutes);
    }

    public function testToStringWhenBoundaryAndCommonTimesThenFormatsWithZeroPadding(): void
    {
        /** @When converting boundary and common times to their string representations */
        $strings = array_map(
            static fn(TimeOfDay $time): string => $time->toString(),
            [
                TimeOfDay::from(hour: 0, minute: 0),
                TimeOfDay::from(hour: 8, minute: 5),
                TimeOfDay::from(hour: 14, minute: 30),
                TimeOfDay::from(hour: 23, minute: 59)
            ]
        );

        /** @Then each time should format with zero-padded hour and minute */
        self::assertSame(['00:00', '08:05', '14:30', '23:59'], $strings);
    }

    public function testToMinutesSinceMidnightWhenEightThirtyThenReturnsFiveHundredTen(): void
    {
        /** @Given 08:30 */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @When converting to minutes since midnight */
        $minutes = $time->toMinutesSinceMidnight();

        /** @Then minutes since midnight should be 510 */
        self::assertSame(510, $minutes);
    }

    public function testFromStringWhenSuffixAfterValidPatternThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with a suffix after a valid HH:MM pattern */
        TimeOfDay::fromString(value: '08:30xyz');
    }

    public function testToMinutesSinceMidnightWhenEndOfDayThenReturnsFourteenThirtyNine(): void
    {
        /** @Given 23:59 */
        $time = TimeOfDay::from(hour: 23, minute: 59);

        /** @When converting to minutes since midnight */
        $minutes = $time->toMinutesSinceMidnight();

        /** @Then minutes since midnight should be 1439 */
        self::assertSame(1439, $minutes);
    }

    public function testFromStringWhenPrefixBeforeValidPatternThenThrowsInvalidTimeOfDay(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with a prefix before a valid HH:MM pattern */
        TimeOfDay::fromString(value: 'abc08:30');
    }
}
