<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Exceptions\InvalidInstant;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Precision;
use TinyBlocks\Time\Timezone;

final class InstantTest extends TestCase
{
    public function testNowThenTimezoneIsUtc(): void
    {
        /** @When creating an Instant from now */
        $instant = Instant::now();

        /** @Then the DateTimeImmutable timezone should be UTC */
        self::assertSame('UTC', $instant->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testIsAfterWhenLaterThenReturnsTrue(): void
    {
        /** @Given a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the later instant is after the earlier */
        $isAfter = $later->isAfter(other: $earlier);

        /** @Then the later instant should be after the earlier */
        self::assertTrue($isAfter);
    }

    public function testIsAfterWhenEqualThenReturnsFalse(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And another Instant at the same moment */
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the instant is after the other */
        $isAfter = $instant->isAfter(other: $same);

        /** @Then isAfter should return false for equal instants */
        self::assertFalse($isAfter);
    }

    public function testIsBeforeWhenEqualThenReturnsFalse(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And another Instant at the same moment */
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the instant is before the other */
        $isBefore = $instant->isBefore(other: $same);

        /** @Then isBefore should return false for equal instants */
        self::assertFalse($isBefore);
    }

    public function testIsBeforeWhenLaterThenReturnsFalse(): void
    {
        /** @Given a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the later instant is before the earlier */
        $isBefore = $later->isBefore(other: $earlier);

        /** @Then the later instant should not be before the earlier */
        self::assertFalse($isBefore);
    }

    public function testIsAfterWhenEarlierThenReturnsFalse(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When checking whether the earlier instant is after the later */
        $isAfter = $earlier->isAfter(other: $later);

        /** @Then the earlier instant should not be after the later */
        self::assertFalse($isAfter);
    }

    public function testIsBeforeWhenEarlierThenReturnsTrue(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When checking whether the earlier instant is before the later */
        $isBefore = $earlier->isBefore(other: $later);

        /** @Then the earlier instant should be before the later */
        self::assertTrue($isBefore);
    }

    public function testNowThenProducesOrderedUtcInstances(): void
    {
        /** @When invoked twice in rapid succession */
        $first = Instant::now();
        $second = Instant::now();

        /** @Then both should be valid Instants in UTC */
        self::assertSame('UTC', $first->toDateTimeImmutable()->getTimezone()->getName());
        self::assertSame('UTC', $second->toDateTimeImmutable()->getTimezone()->getName());

        /** @And the second should not be before the first */
        self::assertGreaterThanOrEqual(
            $first->toDateTimeImmutable()->format('U.u'),
            $second->toDateTimeImmutable()->format('U.u')
        );
    }

    public function testNowThenIso8601HasNoFractionalSeconds(): void
    {
        /** @Given an Instant created from now */
        $instant = Instant::now();

        /** @When formatting as ISO 8601 */
        $iso = $instant->toIso8601();

        /** @Then the output should match YYYY-MM-DDTHH:MM:SS+00:00 without fractions */
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $iso);
    }

    public function testPlusYearsWhenCommonDateThenShiftsYear(): void
    {
        /** @Given an Instant on a common day */
        $instant = Instant::fromString(value: '2026-05-15T10:00:00+00:00');

        /** @When adding two years */
        $shifted = $instant->plusYears(years: 2);

        /** @Then the year is shifted forward and the time of day is preserved */
        self::assertSame('2028-05-15T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testIsAfterOrEqualWhenEqualThenReturnsTrue(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And another Instant at the same moment */
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the instant is after or equal to the other */
        $isAfterOrEqual = $instant->isAfterOrEqual(other: $same);

        /** @Then isAfterOrEqual should return true for equal instants */
        self::assertTrue($isAfterOrEqual);
    }

    public function testIsAfterOrEqualWhenLaterThenReturnsTrue(): void
    {
        /** @Given a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the later instant is after or equal to the earlier */
        $isAfterOrEqual = $later->isAfterOrEqual(other: $earlier);

        /** @Then the later instant should be after or equal to the earlier */
        self::assertTrue($isAfterOrEqual);
    }

    public function testMinusYearsWhenCommonDateThenShiftsYear(): void
    {
        /** @Given an Instant on a common day */
        $instant = Instant::fromString(value: '2026-05-15T10:00:00+00:00');

        /** @When subtracting two years */
        $shifted = $instant->minusYears(years: 2);

        /** @Then the year is shifted backward and the time of day is preserved */
        self::assertSame('2024-05-15T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenNoZoneThenReanchorsInUtc(): void
    {
        /** @Given an Instant on the last day of a 31-day month */
        $instant = Instant::fromString(value: '2026-01-31T10:00:00+00:00');

        /** @When adding one month without a zone, defaulting to UTC */
        $shifted = $instant->plusMonths(months: 1);

        /** @Then the date is shifted and clamped while the time of day is preserved */
        self::assertSame('2026-02-28T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testIsBeforeOrEqualWhenEqualThenReturnsTrue(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And another Instant at the same moment */
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the instant is before or equal to the other */
        $isBeforeOrEqual = $instant->isBeforeOrEqual(other: $same);

        /** @Then isBeforeOrEqual should return true for equal instants */
        self::assertTrue($isBeforeOrEqual);
    }

    public function testPlusWhenZeroDurationThenReturnsSameTime(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding zero Duration */
        $shifted = $instant->plus(duration: Duration::zero());

        /** @Then the result should be the same time */
        self::assertSame('2026-02-17T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testPlusYearsWhenLeapDayThenClampsToLastDay(): void
    {
        /** @Given an Instant on the leap day of a leap year */
        $instant = Instant::fromString(value: '2024-02-29T10:00:00+00:00');

        /** @When adding one year to a common year */
        $shifted = $instant->plusYears(years: 1);

        /** @Then the day is clamped to the last day of February and the time is preserved */
        self::assertSame('2025-02-28T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testIsBeforeOrEqualWhenLaterThenReturnsFalse(): void
    {
        /** @Given a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When checking whether the later instant is before or equal to the earlier */
        $isBeforeOrEqual = $later->isBeforeOrEqual(other: $earlier);

        /** @Then the later instant should not be before or equal to the earlier */
        self::assertFalse($isBeforeOrEqual);
    }

    public function testMinusMonthsWhenNegativeThenShiftsForward(): void
    {
        /** @Given an Instant on the last day of a 31-day month */
        $instant = Instant::fromString(value: '2026-01-31T10:00:00+00:00');

        /** @When subtracting -1 month (equivalent to adding 1) */
        $shifted = $instant->minusMonths(months: -1);

        /** @Then the date is shifted forward and clamped while the time is preserved */
        self::assertSame('2026-02-28T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testMinusWhenZeroDurationThenReturnsSameTime(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting zero Duration */
        $shifted = $instant->minus(duration: Duration::zero());

        /** @Then the result should be the same time */
        self::assertSame('2026-02-17T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testMinusYearsWhenLeapDayThenClampsToLastDay(): void
    {
        /** @Given an Instant on the leap day of a leap year */
        $instant = Instant::fromString(value: '2024-02-29T10:00:00+00:00');

        /** @When subtracting one year to a common year */
        $shifted = $instant->minusYears(years: 1);

        /** @Then the day is clamped to the last day of February and the time is preserved */
        self::assertSame('2023-02-28T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testIsAfterOrEqualWhenEarlierThenReturnsFalse(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When checking whether the earlier instant is after or equal to the later */
        $isAfterOrEqual = $earlier->isAfterOrEqual(other: $later);

        /** @Then the earlier instant should not be after or equal to the later */
        self::assertFalse($isAfterOrEqual);
    }

    public function testIsBeforeOrEqualWhenEarlierThenReturnsTrue(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When checking whether the earlier instant is before or equal to the later */
        $isBeforeOrEqual = $earlier->isBeforeOrEqual(other: $later);

        /** @Then the earlier instant should be before or equal to the later */
        self::assertTrue($isBeforeOrEqual);
    }

    public function testPlusWhenDurationProvidedThenShiftsForward(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a Duration of 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @When adding the Duration */
        $shifted = $instant->plus(duration: $duration);

        /** @Then the result should be 30 minutes later */
        self::assertSame('2026-02-17T10:30:00+00:00', $shifted->toIso8601());
    }

    public function testNowThenMicrosecondComponentIsRepresentable(): void
    {
        /** @Given an Instant created from now */
        $instant = Instant::now();

        /** @When formatting the underlying DateTimeImmutable with microseconds */
        $microseconds = (int)$instant->toDateTimeImmutable()->format('u');

        /** @Then the microsecond component should be representable (six digits available) */
        self::assertGreaterThanOrEqual(0, $microseconds);
        self::assertLessThanOrEqual(999999, $microseconds);
    }

    public function testDurationUntilWhenSameInstantThenReturnsZero(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And another Instant at the same moment */
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When measuring the duration between them */
        $duration = $instant->durationUntil(other: $same);

        /** @Then the duration between them should be zero */
        self::assertSame(0, $duration->toSeconds());
        self::assertTrue($duration->isZero());
    }

    public function testFromStringWhenUtcOffsetThenRemainsUnchanged(): void
    {
        /** @When creating an Instant from a string already in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the ISO 8601 output should remain unchanged */
        self::assertSame('2026-02-17T10:30:00+00:00', $instant->toIso8601());
    }

    public function testMinusWhenDurationProvidedThenShiftsBackward(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And a Duration of 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @When subtracting the Duration */
        $shifted = $instant->minus(duration: $duration);

        /** @Then the result should be 30 minutes earlier */
        self::assertSame('2026-02-17T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testDurationUntilWhenSwappedThenReturnsSameValue(): void
    {
        /** @Given an Instant at 10:00 */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And an Instant at 11:00 */
        $later = Instant::fromString(value: '2026-02-17T11:00:00+00:00');

        /** @When measuring the duration from the earlier to the later */
        $duration = $earlier->durationUntil(other: $later);

        /** @Then the duration should equal the duration measured in the opposite direction */
        self::assertSame($duration->toSeconds(), $later->durationUntil(other: $earlier)->toSeconds());
    }

    public function testMinusMonthsWhenDefaultZoneThenShiftsBackward(): void
    {
        /** @Given an Instant on the last day of a 31-day month */
        $instant = Instant::fromString(value: '2026-03-31T10:00:00+00:00');

        /** @When subtracting one month without a zone, defaulting to UTC */
        $shifted = $instant->minusMonths(months: 1);

        /** @Then the date is shifted backward and clamped while the time is preserved */
        self::assertSame('2026-02-28T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testPlusWhenCrossesDayBoundaryThenReturnsNextDay(): void
    {
        /** @Given an Instant near the end of the day */
        $instant = Instant::fromString(value: '2026-02-17T23:30:00+00:00');

        /** @When adding 1 hour */
        $shifted = $instant->plus(duration: Duration::fromHours(hours: 1));

        /** @Then the result should cross into the next day */
        self::assertSame('2026-02-18T00:30:00+00:00', $shifted->toIso8601());
    }

    public function testFromStringWhenDatabaseFormatThenTimezoneIsUtc(): void
    {
        /** @Given a database date-time string */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    #[DataProvider('invalidStringsDataProvider')]
    public function testFromStringWhenInvalidThenThrowsInvalidInstant(string $value): void
    {
        /** @Then an exception indicating that the value could not be decoded into a valid instant should be thrown */
        $this->expectException(InvalidInstant::class);
        $template = 'The value <%s> could not be decoded into a valid instant.';
        $this->expectExceptionMessage(sprintf($template, $value));

        /** @When trying to create an Instant from the invalid string */
        Instant::fromString(value: $value);
    }

    public function testToIso8601WhenNoPrecisionThenDefaultsToSeconds(): void
    {
        /** @Given an Instant created from a database string with microseconds */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When formatting without specifying a precision */
        $iso = $instant->toIso8601();

        /** @Then the output should match the seconds-only format */
        self::assertSame('2026-02-17T08:27:21+00:00', $iso);
    }

    public function testPlusMonthsWhenZeroThenReturnsEquivalentInstant(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-05-23T10:00:00+00:00');

        /** @When adding zero months */
        $shifted = $instant->plusMonths(months: 0);

        /** @Then the result is an equivalent instant */
        self::assertSame('2026-05-23T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testFromStringWhenNegativeOffsetThenNormalizesToUtc(): void
    {
        /** @When creating an Instant from a string with a negative offset */
        $instant = Instant::fromString(value: '2026-02-17T07:00:00-05:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T12:00:00+00:00', $instant->toIso8601());
    }

    public function testFromStringWhenPositiveOffsetThenNormalizesToUtc(): void
    {
        /** @When creating an Instant from a string with a positive offset */
        $instant = Instant::fromString(value: '2026-02-17T18:00:00+03:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T15:00:00+00:00', $instant->toIso8601());

        /** @And the DateTimeImmutable timezone should be UTC */
        self::assertSame('UTC', $instant->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testPlusWhenLargeDurationThenShiftsForwardCorrectly(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding 1 day */
        $shifted = $instant->plus(duration: Duration::fromDays(days: 1));

        /** @Then the result should be exactly one day later */
        self::assertSame('2026-02-18T10:00:00+00:00', $shifted->toIso8601());
    }

    public function testPlusWhenDurationProvidedThenPreservesUtcTimezone(): void
    {
        /** @Given an Instant in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding a Duration */
        $shifted = $instant->plus(duration: Duration::fromMinutes(minutes: 90));

        /** @Then the result should remain in UTC */
        self::assertSame('UTC', $shifted->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testPlusYearsWhenZoneProvidedThenReanchorsInThatZone(): void
    {
        /** @Given an Instant whose Tokyo local date is the leap day but whose UTC date is not */
        $instant = Instant::fromString(value: '2024-02-28T20:00:00+00:00');

        /** @And the Tokyo timezone */
        $zone = Timezone::from(identifier: 'Asia/Tokyo');

        /** @When adding one year re-anchored in Tokyo */
        $shifted = $instant->plusYears(years: 1, zone: $zone);

        /** @Then the shift follows the Tokyo local date, clamped, and normalizes back to UTC */
        self::assertSame('2025-02-27T20:00:00+00:00', $shifted->toIso8601());
    }

    public function testFromUnixSecondsWhenEpochThenReturnsUnixEpochInUtc(): void
    {
        /** @When creating an Instant from Unix timestamp zero (epoch) */
        $instant = Instant::fromUnixSeconds(seconds: 0);

        /** @Then the ISO 8601 output should be the Unix epoch in UTC */
        self::assertSame('1970-01-01T00:00:00+00:00', $instant->toIso8601());

        /** @And the Unix seconds should be zero */
        self::assertSame(0, $instant->toUnixSeconds());
    }

    public function testMinusWhenCrossesDayBoundaryThenReturnsPreviousDay(): void
    {
        /** @Given an Instant at the start of the day */
        $instant = Instant::fromString(value: '2026-02-17T00:30:00+00:00');

        /** @When subtracting 1 hour */
        $shifted = $instant->minus(duration: Duration::fromHours(hours: 1));

        /** @Then the result should cross into the previous day */
        self::assertSame('2026-02-16T23:30:00+00:00', $shifted->toIso8601());
    }

    public function testMinusWhenDurationProvidedThenPreservesUtcTimezone(): void
    {
        /** @Given an Instant in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting a Duration */
        $shifted = $instant->minus(duration: Duration::fromMinutes(minutes: 90));

        /** @Then the result should remain in UTC */
        self::assertSame('UTC', $shifted->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testPlusMonthsWhenZoneProvidedThenReanchorsInThatZone(): void
    {
        /** @Given an Instant whose local date in Tokyo differs from its UTC date */
        $instant = Instant::fromString(value: '2026-01-30T20:00:00+00:00');

        /** @And the Tokyo timezone */
        $zone = Timezone::from(identifier: 'Asia/Tokyo');

        /** @When adding one month re-anchored in Tokyo */
        $shifted = $instant->plusMonths(months: 1, zone: $zone);

        /** @Then the shift follows the Tokyo local date, clamped, and normalizes back to UTC */
        self::assertSame('2026-02-27T20:00:00+00:00', $shifted->toIso8601());
    }

    public function testPlusWhenPositiveDurationThenResultIsAfterOriginal(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding a positive Duration */
        $later = $instant->plus(duration: Duration::fromMinutes(minutes: 30));

        /** @Then the result should be after the original */
        self::assertTrue($later->isAfter(other: $instant));
        self::assertTrue($instant->isBefore(other: $later));
    }

    public function testToDateTimeImmutableWhenNonUtcOffsetThenReturnsUtc(): void
    {
        /** @Given an Instant created from a string with a non-UTC offset */
        $instant = Instant::fromString(value: '2026-02-17T15:30:00+05:00');

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());

        /** @And the date-time should reflect the UTC-converted value */
        self::assertSame('2026-02-17T10:30:00', $dateTime->format('Y-m-d\TH:i:s'));
    }

    public function testFromStringWhenMaxNegativeOffsetThenNormalizesToUtc(): void
    {
        /** @When creating an Instant from a string with the maximum negative UTC offset (-12:00) */
        $instant = Instant::fromString(value: '2026-02-16T12:00:00-12:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T00:00:00+00:00', $instant->toIso8601());
    }

    public function testFromStringWhenMaxPositiveOffsetThenNormalizesToUtc(): void
    {
        /** @When creating an Instant from a string with the maximum positive UTC offset (+14:00) */
        $instant = Instant::fromString(value: '2026-02-17T14:00:00+14:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T00:00:00+00:00', $instant->toIso8601());
    }

    public function testPlusMonthsWhenMicrosecondsPresentThenPreservesThem(): void
    {
        /** @Given an Instant carrying microsecond precision */
        $instant = Instant::fromString(value: '2026-01-31T12:34:56.789012+00:00');

        /** @When adding one month */
        $shifted = $instant->plusMonths(months: 1);

        /** @Then the microseconds are preserved after the shift and clamp */
        self::assertSame('2026-02-28T12:34:56.789012+00:00', $shifted->toIso8601(precision: Precision::Microseconds));
    }

    public function testMinusWhenPositiveDurationThenResultIsBeforeOriginal(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting a positive Duration */
        $earlier = $instant->minus(duration: Duration::fromMinutes(minutes: 30));

        /** @Then the result should be before the original */
        self::assertTrue($earlier->isBefore(other: $instant));
        self::assertTrue($instant->isAfter(other: $earlier));
    }

    public function testFromUnixSecondsWhenNegativeThenReturnsDateBeforeEpoch(): void
    {
        /** @When creating an Instant from a negative Unix timestamp representing a date before the epoch */
        $instant = Instant::fromUnixSeconds(seconds: -86400);

        /** @Then the ISO 8601 output should be one day before the epoch */
        self::assertSame('1969-12-31T00:00:00+00:00', $instant->toIso8601());

        /** @And the Unix seconds should round-trip correctly */
        self::assertSame(-86400, $instant->toUnixSeconds());
    }

    public function testPlusYearsWhenArgumentIsIntegerMaxThenInvalidLocalDate(): void
    {
        /** @Given an Instant at a known moment */
        $instant = Instant::fromString(value: '2026-05-15T10:00:00+00:00');

        /** @Then an out-of-range shift is raised instead of an arithmetic overflow */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding the maximum integer number of years re-anchored in UTC */
        $instant->plusYears(years: PHP_INT_MAX);
    }

    public function testToIso8601WhenFromValidStringThenHasNoFractionalSeconds(): void
    {
        /** @Given an Instant created from any valid input */
        $instant = Instant::fromString(value: '2026-06-15T23:59:59+00:00');

        /** @When formatting as ISO 8601 */
        $iso = $instant->toIso8601();

        /** @Then the output should match YYYY-MM-DDTHH:MM:SS+00:00 without fractions */
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $iso);
    }

    public function testToDateTimeImmutableWhenFromUnixSecondsThenTimezoneIsUtc(): void
    {
        /** @Given an Instant created from Unix seconds */
        $instant = Instant::fromUnixSeconds(seconds: 1771324200);

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    #[DataProvider('precisionDataProvider')]
    public function testToIso8601WhenPrecisionProvidedThenMatchesExpectedString(
        string $value,
        Precision $precision,
        string $expectedIso8601
    ): void {
        /** @Given an Instant created from the string */
        $instant = Instant::fromString(value: $value);

        /** @When formatting with the given precision */
        $iso = $instant->toIso8601(precision: $precision);

        /** @Then the output should match the expected ISO 8601 string */
        self::assertSame($expectedIso8601, $iso);
    }

    public function testNowWhenSurroundedByTimeReadingsThenUnixSecondsIsInWindow(): void
    {
        /** @Given the current Unix timestamp before creating the Instant */
        $before = time();

        /** @When creating an Instant from now */
        $instant = Instant::now();

        /** @And capturing the Unix timestamp after */
        $after = time();

        /** @Then the Instant's Unix seconds should be within the before/after window */
        self::assertGreaterThanOrEqual($before, $instant->toUnixSeconds());
        self::assertLessThanOrEqual($after, $instant->toUnixSeconds());
    }

    public function testPlusWhenFollowedByMinusOfSameDurationThenReturnsOriginal(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a Duration of 45 minutes */
        $duration = Duration::fromMinutes(minutes: 45);

        /** @When adding and then subtracting the same Duration */
        $shifted = $instant->plus(duration: $duration)->minus(duration: $duration);

        /** @Then the result should be the original time */
        self::assertSame($instant->toIso8601(), $shifted->toIso8601());
        self::assertSame($instant->toUnixSeconds(), $shifted->toUnixSeconds());
    }

    public function testComparisonsWhenSameMomentFromDifferentOriginsThenAllAgree(): void
    {
        /** @Given an Instant from a string with offset */
        $fromString = Instant::fromString(value: '2026-02-17T13:30:00-03:00');

        /** @And an Instant from the equivalent Unix seconds */
        $fromUnix = Instant::fromUnixSeconds(seconds: $fromString->toUnixSeconds());

        /** @When checking whether the string-built instant is before the Unix-built one */
        $isBefore = $fromString->isBefore(other: $fromUnix);

        /** @Then both should be equal by all comparison methods */
        self::assertFalse($isBefore);
        self::assertFalse($fromString->isAfter(other: $fromUnix));
        self::assertTrue($fromString->isBeforeOrEqual(other: $fromUnix));
        self::assertTrue($fromString->isAfterOrEqual(other: $fromUnix));
    }

    public function testDurationUntilWhenInstantsDifferThenReturnsAbsoluteDistance(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant 30 minutes apart */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When measuring the duration between them */
        $duration = $earlier->durationUntil(other: $later);

        /** @Then the duration should be 1800 seconds regardless of direction */
        self::assertSame(1800, $duration->toSeconds());
        self::assertSame(1800, $later->durationUntil(other: $earlier)->toSeconds());
    }

    public function testPlusMonthsWhenResultExceedsMaximumYearThenInvalidLocalDate(): void
    {
        /** @Given an Instant in the last month of the maximum supported year */
        $instant = Instant::fromString(value: '9999-12-01T10:00:00+00:00');

        /** @Then an exception indicating an out-of-range shift should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding one month crosses above the maximum year */
        $instant->plusMonths(months: 1);
    }

    public function testDurationUntilWhenAcrossDayBoundaryThenReturnsCorrectSeconds(): void
    {
        /** @Given an Instant before midnight */
        $before = Instant::fromString(value: '2026-02-17T23:00:00+00:00');

        /** @And an Instant after midnight */
        $after = Instant::fromString(value: '2026-02-18T01:00:00+00:00');

        /** @When measuring the duration between them */
        $duration = $before->durationUntil(other: $after);

        /** @Then the duration should be 7200 seconds (2 hours) */
        self::assertSame(7200, $duration->toSeconds());
    }

    public function testFromStringWhenIso8601WithFractionalSecondsThenTimezoneIsUtc(): void
    {
        /** @Given an ISO 8601 string with microseconds and a UTC offset */
        $instant = Instant::fromString(value: '2026-05-23T12:55:10.272097+00:00');

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    #[DataProvider('validDatabaseStringsDataProvider')]
    public function testFromStringWhenValidDatabaseFormatThenMatchesExpectedIso8601(
        string $value,
        string $expectedIso8601
    ): void {
        /** @When creating an Instant from the database string */
        $instant = Instant::fromString(value: $value);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());
    }

    #[DataProvider('validStringsDataProvider')]
    public function testFromStringWhenValidThenMatchesExpectedIso8601AndUnixSeconds(
        string $value,
        string $expectedIso8601,
        int $expectedUnixSeconds
    ): void {
        /** @When creating an Instant from the string */
        $instant = Instant::fromString(value: $value);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());

        /** @And the Unix seconds should match the expected timestamp */
        self::assertSame($expectedUnixSeconds, $instant->toUnixSeconds());
    }

    public function testIsBeforeAndIsAfterWhenInstantsDifferThenAreMutuallyExclusive(): void
    {
        /** @Given an earlier Instant */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a later Instant */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When checking whether the earlier instant is before the later */
        $isBefore = $earlier->isBefore(other: $later);

        /** @Then isBefore and isAfter should be mutually exclusive */
        self::assertTrue($isBefore);
        self::assertFalse($earlier->isAfter(other: $later));
        self::assertTrue($later->isAfter(other: $earlier));
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testDurationUntilWhenSameMomentFromDifferentOriginsThenReturnsZero(): void
    {
        /** @Given an Instant from a string with offset */
        $fromString = Instant::fromString(value: '2026-02-17T13:30:00-03:00');

        /** @And an Instant from the equivalent Unix seconds */
        $fromUnix = Instant::fromUnixSeconds(seconds: $fromString->toUnixSeconds());

        /** @When measuring the duration between them */
        $duration = $fromString->durationUntil(other: $fromUnix);

        /** @Then the duration between them should be zero */
        self::assertTrue($duration->isZero());
    }

    public function testPlusMonthsWhenTargetLocalTimeFallsInDstGapThenPhpShiftsForward(): void
    {
        /** @Given an Instant whose local time in New York is 2026-02-08 02:30 */
        $instant = Instant::fromString(value: '2026-02-08T07:30:00+00:00');

        /** @And the America/New_York timezone */
        $zone = Timezone::from(identifier: 'America/New_York');

        /** @When adding one month lands on the non-existent local 2026-03-08 02:30 spring-forward gap */
        $shifted = $instant->plusMonths(months: 1, zone: $zone);

        /** @Then PHP shifts the local time forward to 03:30 -04:00, which is 07:30 UTC */
        self::assertSame('2026-03-08T07:30:00+00:00', $shifted->toIso8601());
    }

    public function testDurationUntilWhenInstantShiftedByDurationThenEqualsThatDuration(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a Duration of 90 minutes */
        $duration = Duration::fromMinutes(minutes: 90);

        /** @And the Instant shifted forward by the Duration */
        $shifted = $instant->plus(duration: $duration);

        /** @When measuring the duration until the shifted Instant */
        $measured = $instant->durationUntil(other: $shifted);

        /** @Then the durationUntil should equal the original Duration */
        self::assertSame($duration->toSeconds(), $measured->toSeconds());
    }

    public function testFromStringWhenEquivalentToFromUnixSecondsThenProducesSameResult(): void
    {
        /** @Given an Instant created from a string */
        $fromString = Instant::fromString(value: '2026-02-17T00:00:00+00:00');

        /** @And an Instant created from the equivalent Unix seconds */
        $fromUnix = Instant::fromUnixSeconds(seconds: $fromString->toUnixSeconds());

        /** @Then both should produce the same ISO 8601 output */
        self::assertSame($fromString->toIso8601(), $fromUnix->toIso8601());

        /** @And both should produce the same Unix seconds */
        self::assertSame($fromString->toUnixSeconds(), $fromUnix->toUnixSeconds());
    }

    #[DataProvider('unixSecondsDataProvider')]
    public function testFromUnixSecondsWhenValidThenMatchesExpectedIso8601AndRoundTrips(
        int $seconds,
        string $expectedIso8601
    ): void {
        /** @When creating an Instant from Unix seconds */
        $instant = Instant::fromUnixSeconds(seconds: $seconds);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());

        /** @And the Unix seconds should round-trip correctly */
        self::assertSame($seconds, $instant->toUnixSeconds());
    }

    public function testToDateTimeImmutableWhenFromStringThenSupportsMicrosecondPrecision(): void
    {
        /** @Given an Instant created from a valid string */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the format should support microsecond precision */
        $formatted = $dateTime->format('Y-m-d\TH:i:s.u');
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}$/', $formatted);
    }

    public function testFromStringWhenOffsetCrossesDayBoundaryThenReflectsPreviousDayInUtc(): void
    {
        /** @When creating an Instant from a string where the UTC conversion crosses a day boundary */
        $instant = Instant::fromString(value: '2026-02-18T01:00:00+03:00');

        /** @Then the ISO 8601 output should reflect the previous day in UTC */
        self::assertSame('2026-02-17T22:00:00+00:00', $instant->toIso8601());
    }

    public function testToIso8601WhenUnixSecondsWithMicrosecondsPrecisionThenSixZeroDigits(): void
    {
        /** @Given an Instant created from Unix seconds (no sub-second precision) */
        $instant = Instant::fromUnixSeconds(seconds: 1771324200);

        /** @When formatting with microsecond precision */
        $iso = $instant->toIso8601(precision: Precision::Microseconds);

        /** @Then the output should contain six zero fractional digits */
        self::assertSame('2026-02-17T10:30:00.000000+00:00', $iso);
    }

    public function testFromStringWhenIso8601WithFractionalSecondsThenPreservesMicroseconds(): void
    {
        /** @Given an ISO 8601 string with full microsecond precision */
        $instant = Instant::fromString(value: '2026-05-23T12:55:10.272097+00:00');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the microseconds should be preserved */
        self::assertSame('272097', $dateTime->format('u'));
    }

    public function testToIso8601WhenUnixSecondsWithMillisecondsPrecisionThenThreeZeroDigits(): void
    {
        /** @Given an Instant created from Unix seconds (no sub-second precision) */
        $instant = Instant::fromUnixSeconds(seconds: 1771324200);

        /** @When formatting with millisecond precision */
        $iso = $instant->toIso8601(precision: Precision::Milliseconds);

        /** @Then the output should contain three zero fractional digits */
        self::assertSame('2026-02-17T10:30:00.000+00:00', $iso);
    }

    public function testFromStringWhenDatabaseFormatWithMicrosecondsThenPreservesMicroseconds(): void
    {
        /** @Given a database date-time string with microsecond precision */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the microseconds should be preserved */
        self::assertSame('106011', $dateTime->format('u'));
    }

    public function testFromStringWhenDatabaseFormatWithoutMicrosecondsThenMicrosecondsAreZero(): void
    {
        /** @Given a database date-time string without microseconds */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the microseconds should be zero */
        self::assertSame('000000', $dateTime->format('u'));
    }

    public function testFromStringWhenIso8601WithFractionalSecondsAndOffsetThenNormalizesToUtc(): void
    {
        /** @Given an ISO 8601 string with microseconds and a non-UTC offset */
        $instant = Instant::fromString(value: '2026-02-17T13:30:00.500000-03:00');

        /** @When formatting as ISO 8601 */
        $iso = $instant->toIso8601();

        /** @Then the output should be normalized to UTC without fractions */
        self::assertSame('2026-02-17T16:30:00+00:00', $iso);
    }

    public function testToIso8601WhenDatabaseInputWithMicrosecondsPrecisionThenIncludesSixDigits(): void
    {
        /** @Given an Instant created from a database string with known microseconds */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When formatting with microsecond precision */
        $iso = $instant->toIso8601(precision: Precision::Microseconds);

        /** @Then the output should include all six fractional digits */
        self::assertSame('2026-02-17T08:27:21.106011+00:00', $iso);
    }

    public function testToIso8601WhenIso8601InputWithMicrosecondsPrecisionThenRoundTripsFraction(): void
    {
        /** @Given an Instant parsed from an ISO 8601 string with microseconds */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00.123456+00:00');

        /** @When formatting with microsecond precision */
        $iso = $instant->toIso8601(precision: Precision::Microseconds);

        /** @Then the output should be byte-identical to the original fractional string */
        self::assertSame('2026-02-17T10:30:00.123456+00:00', $iso);
    }

    public function testToIso8601WhenDatabaseInputWithMillisecondsPrecisionThenIncludesThreeDigits(): void
    {
        /** @Given an Instant created from a database string with known microseconds */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When formatting with millisecond precision */
        $iso = $instant->toIso8601(precision: Precision::Milliseconds);

        /** @Then the output should include exactly three fractional digits */
        self::assertSame('2026-02-17T08:27:21.106+00:00', $iso);
    }

    public function testToIso8601WhenIso8601InputWithMillisecondsPrecisionThenTruncatesToThreeDigits(): void
    {
        /** @Given an Instant parsed from an ISO 8601 string with microseconds */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00.123456+00:00');

        /** @When formatting with millisecond precision */
        $iso = $instant->toIso8601(precision: Precision::Milliseconds);

        /** @Then the output should truncate to three fractional digits */
        self::assertSame('2026-02-17T10:30:00.123+00:00', $iso);
    }

    public static function precisionDataProvider(): array
    {
        return [
            'Seconds precision, no fractions emitted'                 => [
                'value'           => '2026-02-17 08:27:21.106011',
                'precision'       => Precision::Seconds,
                'expectedIso8601' => '2026-02-17T08:27:21+00:00'
            ],
            'Microseconds precision, six-digit fraction'              => [
                'value'           => '2026-02-17 08:27:21.106011',
                'precision'       => Precision::Microseconds,
                'expectedIso8601' => '2026-02-17T08:27:21.106011+00:00'
            ],
            'Milliseconds precision, three-digit fraction'            => [
                'value'           => '2026-02-17 08:27:21.106011',
                'precision'       => Precision::Milliseconds,
                'expectedIso8601' => '2026-02-17T08:27:21.106+00:00'
            ],
            'Microseconds precision, three-digit input zero-padded'   => [
                'value'           => '2026-02-17T10:30:00.272+00:00',
                'precision'       => Precision::Microseconds,
                'expectedIso8601' => '2026-02-17T10:30:00.272000+00:00'
            ],
            'Milliseconds precision, three-digit input round-trips'   => [
                'value'           => '2026-02-17T10:30:00.272+00:00',
                'precision'       => Precision::Milliseconds,
                'expectedIso8601' => '2026-02-17T10:30:00.272+00:00'
            ],
            'Seconds precision strips fractions from ISO 8601 input'  => [
                'value'           => '2026-02-17T10:30:00.123456+00:00',
                'precision'       => Precision::Seconds,
                'expectedIso8601' => '2026-02-17T10:30:00+00:00'
            ]
        ];
    }

    public static function unixSecondsDataProvider(): array
    {
        return [
            'Epoch'                  => [
                'seconds'         => 0,
                'expectedIso8601' => '1970-01-01T00:00:00+00:00'
            ],
            'Year 2000 midnight'     => [
                'seconds'         => 946684800,
                'expectedIso8601' => '2000-01-01T00:00:00+00:00'
            ],
            'One day after epoch'    => [
                'seconds'         => 86400,
                'expectedIso8601' => '1970-01-02T00:00:00+00:00'
            ],
            'One day before epoch'   => [
                'seconds'         => -86400,
                'expectedIso8601' => '1969-12-31T00:00:00+00:00'
            ],
            'Year 2026 reference'    => [
                'seconds'         => 1771324200,
                'expectedIso8601' => '2026-02-17T10:30:00+00:00'
            ],
            'Large future timestamp' => [
                'seconds'         => 2147483647,
                'expectedIso8601' => '2038-01-19T03:14:07+00:00'
            ]
        ];
    }

    public static function validStringsDataProvider(): array
    {
        return [
            'UTC offset'                        => [
                'value'               => '2026-02-17T10:30:00+00:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Midnight UTC'                      => [
                'value'               => '2026-01-01T00:00:00+00:00',
                'expectedIso8601'     => '2026-01-01T00:00:00+00:00',
                'expectedUnixSeconds' => 1767225600
            ],
            'End of day UTC'                    => [
                'value'               => '2026-02-17T23:59:59+00:00',
                'expectedIso8601'     => '2026-02-17T23:59:59+00:00',
                'expectedUnixSeconds' => 1771372799
            ],
            'Positive offset +05:30'            => [
                'value'               => '2026-02-17T16:00:00+05:30',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -03:00'            => [
                'value'               => '2026-02-17T07:30:00-03:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -05:00'            => [
                'value'               => '2026-02-17T05:30:00-05:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Positive offset +09:00'            => [
                'value'               => '2026-02-17T19:30:00+09:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -09:30'            => [
                'value'               => '2026-02-17T01:00:00-09:30',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'UTC offset with microseconds'      => [
                'value'               => '2026-02-17T10:30:00.123456+00:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'UTC offset with short fraction'    => [
                'value'               => '2026-02-17T10:30:00.272+00:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Positive offset with microseconds' => [
                'value'               => '2026-02-17T16:00:00.500000+05:30',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Zulu designator'                   => [
                'value'               => '2026-02-17T10:30:00Z',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Zulu designator at midnight'       => [
                'value'               => '2026-01-01T00:00:00Z',
                'expectedIso8601'     => '2026-01-01T00:00:00+00:00',
                'expectedUnixSeconds' => 1767225600
            ],
            'Zulu with microseconds'            => [
                'value'               => '2026-02-17T10:30:00.123456Z',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Zulu with short fraction'          => [
                'value'               => '2026-02-17T10:30:00.272Z',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ]
        ];
    }

    public static function invalidStringsDataProvider(): array
    {
        return [
            'Date only'                              => ['value' => '2026-02-17'],
            'Time only'                              => ['value' => '10:30:00'],
            'Plain text'                             => ['value' => 'not-a-date'],
            'Invalid day'                            => ['value' => '2026-02-30T10:30:00+00:00'],
            'Empty string'                           => ['value' => ''],
            'Invalid month'                          => ['value' => '2026-13-17T10:30:00+00:00'],
            'Missing offset'                         => ['value' => '2026-02-17T10:30:00'],
            'Truncated offset'                       => ['value' => '2026-02-17T10:30:00+00'],
            'Slash-separated date'                   => ['value' => '2026/02/17T10:30:00+00:00'],
            'Missing time separator'                 => ['value' => '2026-02-17 10:30:00+00:00'],
            'Lowercase zulu designator'              => ['value' => '2026-02-17T10:30:00z'],
            'Unix timestamp as string'               => ['value' => '1771324200'],
            'Database format with invalid day'       => ['value' => '2026-02-30 08:27:21.106011'],
            'Database format with T separator'       => ['value' => '2026-02-17T08:27:21.106011'],
            'Database format with invalid month'     => ['value' => '2026-13-17 08:27:21.106011'],
            'Fractional ISO 8601 with invalid day'   => ['value' => '2026-02-30T10:30:00.000000+00:00'],
            'Fractional ISO 8601 with invalid month' => ['value' => '2026-13-17T10:30:00.123456+00:00']
        ];
    }

    public static function validDatabaseStringsDataProvider(): array
    {
        return [
            'End of day'                    => [
                'value'           => '2026-12-31 23:59:59.999999',
                'expectedIso8601' => '2026-12-31T23:59:59+00:00'
            ],
            'Full microseconds'             => [
                'value'           => '2026-02-17 08:27:21.106011',
                'expectedIso8601' => '2026-02-17T08:27:21+00:00'
            ],
            'Midnight with zeros'           => [
                'value'           => '2026-01-01 00:00:00.000000',
                'expectedIso8601' => '2026-01-01T00:00:00+00:00'
            ],
            'Without microseconds'          => [
                'value'           => '2026-02-17 08:27:21',
                'expectedIso8601' => '2026-02-17T08:27:21+00:00'
            ],
            'Three digit fraction'          => [
                'value'           => '2026-02-17 08:27:21.106',
                'expectedIso8601' => '2026-02-17T08:27:21+00:00'
            ],
            'Single digit fraction'         => [
                'value'           => '2026-02-17 08:27:21.1',
                'expectedIso8601' => '2026-02-17T08:27:21+00:00'
            ],
            'Midnight without microseconds' => [
                'value'           => '2026-01-01 00:00:00',
                'expectedIso8601' => '2026-01-01T00:00:00+00:00'
            ]
        ];
    }
}
