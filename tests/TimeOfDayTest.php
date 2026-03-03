<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Internal\Exceptions\InvalidTimeOfDay;
use TinyBlocks\Time\TimeOfDay;

final class TimeOfDayTest extends TestCase
{
    public function testTimeOfDayFromCreatesValidTimeOfDay(): void
    {
        /** @Given valid hour and minute */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @Then the components should match */
        self::assertSame(8, $time->hour);
        self::assertSame(30, $time->minute);
    }

    public function testTimeOfDayFromWithZeros(): void
    {
        /** @Given hour 0 and minute 0 */
        $time = TimeOfDay::from(hour: 0, minute: 0);

        /** @Then it should be midnight */
        self::assertSame(0, $time->hour);
        self::assertSame(0, $time->minute);
    }

    public function testTimeOfDayFromWithMaxValues(): void
    {
        /** @Given maximum valid hour and minute */
        $time = TimeOfDay::from(hour: 23, minute: 59);

        /** @Then the components should match */
        self::assertSame(23, $time->hour);
        self::assertSame(59, $time->minute);
    }

    public function testTimeOfDayWhenHourIsNegative(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with negative hour */
        TimeOfDay::from(hour: -1, minute: 0);
    }

    public function testTimeOfDayWhenHourExceeds23(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with hour 24 */
        TimeOfDay::from(hour: 24, minute: 0);
    }

    public function testTimeOfDayWhenMinuteIsNegative(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with negative minute */
        TimeOfDay::from(hour: 10, minute: -1);
    }

    public function testTimeOfDayWhenMinuteExceeds59(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When creating with minute 60 */
        TimeOfDay::from(hour: 10, minute: 60);
    }

    public function testTimeOfDayMidnight(): void
    {
        /** @Given midnight */
        $time = TimeOfDay::midnight();

        /** @Then it should be 00:00 */
        self::assertSame(0, $time->hour);
        self::assertSame(0, $time->minute);
        self::assertSame('00:00', $time->toString());
    }

    public function testTimeOfDayNoon(): void
    {
        /** @Given noon */
        $time = TimeOfDay::noon();

        /** @Then it should be 12:00 */
        self::assertSame(12, $time->hour);
        self::assertSame(0, $time->minute);
        self::assertSame('12:00', $time->toString());
    }

    public function testTimeOfDayFromInstant(): void
    {
        /** @Given an Instant at 14:30 UTC */
        $instant = Instant::fromString(value: '2026-02-17T14:30:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then the components should match */
        self::assertSame(14, $time->hour);
        self::assertSame(30, $time->minute);
    }

    public function testTimeOfDayFromInstantAtMidnight(): void
    {
        /** @Given an Instant at midnight */
        $instant = Instant::fromString(value: '2026-02-17T00:00:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then it should be midnight */
        self::assertSame(0, $time->hour);
        self::assertSame(0, $time->minute);
    }

    public function testTimeOfDayFromInstantAtEndOfDay(): void
    {
        /** @Given an Instant at 23:59 */
        $instant = Instant::fromString(value: '2026-02-17T23:59:00+00:00');

        /** @When extracting the time of day */
        $time = TimeOfDay::fromInstant(instant: $instant);

        /** @Then it should be 23:59 */
        self::assertSame(23, $time->hour);
        self::assertSame(59, $time->minute);
    }

    public function testTimeOfDayFromStringValid(): void
    {
        /** @Given a valid time string */
        $time = TimeOfDay::fromString(value: '08:30');

        /** @Then the components should match */
        self::assertSame(8, $time->hour);
        self::assertSame(30, $time->minute);
    }

    public function testTimeOfDayFromStringMidnight(): void
    {
        /** @Given midnight as string */
        $time = TimeOfDay::fromString(value: '00:00');

        /** @Then it should be midnight */
        self::assertSame(0, $time->hour);
        self::assertSame(0, $time->minute);
    }

    public function testTimeOfDayFromStringEndOfDay(): void
    {
        /** @Given end of day as string */
        $time = TimeOfDay::fromString(value: '23:59');

        /** @Then it should be 23:59 */
        self::assertSame(23, $time->hour);
        self::assertSame(59, $time->minute);
    }

    public function testTimeOfDayFromStringWhenInvalidFormat(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing an invalid string */
        TimeOfDay::fromString(value: '8:30');
    }

    public function testTimeOfDayFromStringWhenEmpty(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing an empty string */
        TimeOfDay::fromString(value: '');
    }

    public function testTimeOfDayFromStringWhenHasSeconds(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with seconds */
        TimeOfDay::fromString(value: '08:30:00');
    }

    public function testTimeOfDayFromStringWhenHourOutOfRange(): void
    {
        /** @Then an exception indicating that hour is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with hour 25 */
        TimeOfDay::fromString(value: '25:00');
    }

    public function testTimeOfDayFromStringWhenMinuteOutOfRange(): void
    {
        /** @Then an exception indicating that minute is out of range should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with minute 60 */
        TimeOfDay::fromString(value: '10:60');
    }

    public function testTimeOfDayToMinutesSinceMidnightAtMidnight(): void
    {
        /** @Given midnight */
        $time = TimeOfDay::midnight();

        /** @Then minutes since midnight should be 0 */
        self::assertSame(0, $time->toMinutesSinceMidnight());
    }

    public function testTimeOfDayToMinutesSinceMidnightAtNoon(): void
    {
        /** @Given noon */
        $time = TimeOfDay::noon();

        /** @Then minutes since midnight should be 720 */
        self::assertSame(720, $time->toMinutesSinceMidnight());
    }

    public function testTimeOfDayToMinutesSinceMidnightAt0830(): void
    {
        /** @Given 08:30 */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @Then minutes since midnight should be 510 */
        self::assertSame(510, $time->toMinutesSinceMidnight());
    }

    public function testTimeOfDayToMinutesSinceMidnightAtEndOfDay(): void
    {
        /** @Given 23:59 */
        $time = TimeOfDay::from(hour: 23, minute: 59);

        /** @Then minutes since midnight should be 1439 */
        self::assertSame(1439, $time->toMinutesSinceMidnight());
    }

    public function testTimeOfDayToDuration(): void
    {
        /** @Given 08:30 */
        $time = TimeOfDay::from(hour: 8, minute: 30);

        /** @When converting to Duration */
        $duration = $time->toDuration();

        /** @Then the duration should be 510 minutes in seconds */
        self::assertSame(510, $duration->toMinutes());
        self::assertSame(30600, $duration->toSeconds());
    }

    public function testTimeOfDayToDurationAtMidnight(): void
    {
        /** @Given midnight */
        $time = TimeOfDay::midnight();

        /** @When converting to Duration */
        $duration = $time->toDuration();

        /** @Then the duration should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testTimeOfDayIsBeforeReturnsTrueWhenEarlier(): void
    {
        /** @Given an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @And a later time */
        $later = TimeOfDay::from(hour: 14, minute: 30);

        /** @Then the earlier should be before the later */
        self::assertTrue($earlier->isBefore(other: $later));
    }

    public function testTimeOfDayIsBeforeReturnsFalseWhenLater(): void
    {
        /** @Given a later time */
        $later = TimeOfDay::from(hour: 14, minute: 30);

        /** @And an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @Then the later should not be before the earlier */
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testTimeOfDayIsBeforeReturnsFalseWhenEqual(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @Then isBefore should return false */
        self::assertFalse($time->isBefore(other: $same));
    }

    public function testTimeOfDayIsAfterReturnsTrueWhenLater(): void
    {
        /** @Given a later time */
        $later = TimeOfDay::from(hour: 18, minute: 0);

        /** @And an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @Then the later should be after the earlier */
        self::assertTrue($later->isAfter(other: $earlier));
    }

    public function testTimeOfDayIsAfterReturnsFalseWhenEqual(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @Then isAfter should return false */
        self::assertFalse($time->isAfter(other: $same));
    }

    public function testTimeOfDayIsBeforeOrEqualReturnsTrueWhenEqual(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @Then isBeforeOrEqual should return true */
        self::assertTrue($time->isBeforeOrEqual(other: $same));
    }

    public function testTimeOfDayIsAfterOrEqualReturnsTrueWhenEqual(): void
    {
        /** @Given a time */
        $time = TimeOfDay::from(hour: 10, minute: 0);

        /** @And the same time */
        $same = TimeOfDay::from(hour: 10, minute: 0);

        /** @Then isAfterOrEqual should return true */
        self::assertTrue($time->isAfterOrEqual(other: $same));
    }

    public function testTimeOfDayIsBeforeAndIsAfterAreMutuallyExclusive(): void
    {
        /** @Given an earlier time */
        $earlier = TimeOfDay::from(hour: 8, minute: 0);

        /** @And a later time */
        $later = TimeOfDay::from(hour: 18, minute: 0);

        /** @Then isBefore and isAfter should be mutually exclusive */
        self::assertTrue($earlier->isBefore(other: $later));
        self::assertFalse($earlier->isAfter(other: $later));
        self::assertTrue($later->isAfter(other: $earlier));
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testTimeOfDayDurationUntilReturnsCorrectDuration(): void
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

    public function testTimeOfDayDurationUntilWhenEndIsBeforeStart(): void
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

    public function testTimeOfDayDurationUntilWhenEqual(): void
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

    public function testTimeOfDayToStringFormatsCorrectly(): void
    {
        /** @Then various times should format correctly */
        self::assertSame('00:00', TimeOfDay::from(hour: 0, minute: 0)->toString());
        self::assertSame('08:05', TimeOfDay::from(hour: 8, minute: 5)->toString());
        self::assertSame('14:30', TimeOfDay::from(hour: 14, minute: 30)->toString());
        self::assertSame('23:59', TimeOfDay::from(hour: 23, minute: 59)->toString());
    }

    public function testTimeOfDayFromStringAndToStringRoundTrip(): void
    {
        /** @Given a time string */
        $original = '14:30';

        /** @When parsing and formatting back */
        $result = TimeOfDay::fromString(value: $original)->toString();

        /** @Then the result should match the original */
        self::assertSame($original, $result);
    }

    public function testTimeOfDayFromInstantAndFromProduceSameResult(): void
    {
        /** @Given an Instant at 14:30 UTC */
        $instant = Instant::fromString(value: '2026-02-17T14:30:00+00:00');

        /** @When creating from both methods */
        $fromInstant = TimeOfDay::fromInstant(instant: $instant);

        /** @And creating from hour and minute directly */
        $fromFactory = TimeOfDay::from(hour: 14, minute: 30);

        /** @Then both should produce the same result */
        self::assertSame($fromInstant->hour, $fromFactory->hour);
        self::assertSame($fromInstant->minute, $fromFactory->minute);
    }

    public function testTimeOfDayFromStringWhenPrefixBeforeValidPattern(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with a prefix before a valid HH:MM pattern */
        TimeOfDay::fromString(value: 'abc08:30');
    }

    public function testTimeOfDayFromStringWhenSuffixAfterValidPattern(): void
    {
        /** @Then an exception indicating that the format is invalid should be thrown */
        $this->expectException(InvalidTimeOfDay::class);

        /** @When parsing a string with a suffix after a valid HH:MM pattern */
        TimeOfDay::fromString(value: '08:30xyz');
    }
}
