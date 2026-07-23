<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\DayOfWeek;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\LocalDate;
use TinyBlocks\Time\Timezone;

final class LocalDateTest extends TestCase
{
    public function testIsAfterWhenAfterThenTrue(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether the later date is after the earlier */
        $isAfter = $later->isAfter(other: $earlier);

        /** @Then the later date is after the earlier */
        self::assertTrue($isAfter);
    }

    public function testIsAfterWhenEqualThenFalse(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether one date is after the other */
        $isAfter = $first->isAfter(other: $second);

        /** @Then isAfter returns false for equal dates */
        self::assertFalse($isAfter);
    }

    public function testIsAfterWhenBeforeThenFalse(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @When checking whether the earlier date is after the later */
        $isAfter = $earlier->isAfter(other: $later);

        /** @Then the earlier date is not after the later */
        self::assertFalse($isAfter);
    }

    public function testIsBeforeWhenAfterThenFalse(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether the later date is before the earlier */
        $isBefore = $later->isBefore(other: $earlier);

        /** @Then the later date is not before the earlier */
        self::assertFalse($isBefore);
    }

    public function testIsBeforeWhenBeforeThenTrue(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @When checking whether the earlier date is before the later */
        $isBefore = $earlier->isBefore(other: $later);

        /** @Then the earlier date is before the later */
        self::assertTrue($isBefore);
    }

    public function testIsBeforeWhenEqualThenFalse(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether one date is before the other */
        $isBefore = $first->isBefore(other: $second);

        /** @Then isBefore returns false for equal dates */
        self::assertFalse($isBefore);
    }

    public function testIsAfterOrEqualWhenAfterThenTrue(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether the later date is after or equal to the earlier */
        $isAfterOrEqual = $later->isAfterOrEqual(other: $earlier);

        /** @Then the later date is after or equal to the earlier */
        self::assertTrue($isAfterOrEqual);
    }

    public function testIsAfterOrEqualWhenEqualThenTrue(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether one date is after or equal to the other */
        $isAfterOrEqual = $first->isAfterOrEqual(other: $second);

        /** @Then isAfterOrEqual returns true for equal dates */
        self::assertTrue($isAfterOrEqual);
    }

    public function testIsBeforeOrEqualWhenEqualThenTrue(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether one date is before or equal to the other */
        $isBeforeOrEqual = $first->isBeforeOrEqual(other: $second);

        /** @Then isBeforeOrEqual returns true for equal dates */
        self::assertTrue($isBeforeOrEqual);
    }

    public function testIsAfterOrEqualWhenBeforeThenFalse(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @When checking whether the earlier date is after or equal to the later */
        $isAfterOrEqual = $earlier->isAfterOrEqual(other: $later);

        /** @Then the earlier date is not after or equal to the later */
        self::assertFalse($isAfterOrEqual);
    }

    public function testIsBeforeOrEqualWhenAfterThenFalse(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When checking whether the later date is before or equal to the earlier */
        $isBeforeOrEqual = $later->isBeforeOrEqual(other: $earlier);

        /** @Then the later date is not before or equal to the earlier */
        self::assertFalse($isBeforeOrEqual);
    }

    public function testIsBeforeOrEqualWhenBeforeThenTrue(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @When checking whether the earlier date is before or equal to the later */
        $isBeforeOrEqual = $earlier->isBeforeOrEqual(other: $later);

        /** @Then the earlier date is before or equal to the later */
        self::assertTrue($isBeforeOrEqual);
    }

    public function testOfWhenDayZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <1>, and day <0> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with day zero */
        LocalDate::of(year: 2026, month: 1, day: 0);
    }

    public function testOfWhenYearZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <0>, month <1>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with year zero */
        LocalDate::of(year: 0, month: 1, day: 1);
    }

    public function testOfWhenMonthZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <0>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with month zero */
        LocalDate::of(year: 2026, month: 0, day: 1);
    }

    public function testPlusDaysWhenZeroThenReturnsSameDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding zero days */
        $shifted = $date->plusDays(days: 0);

        /** @Then the result is the same date */
        self::assertSame('2026-05-23', $shifted->toIso8601());
    }

    public function testDayOfWeekWhenMondayThenReturnsMonday(): void
    {
        /** @Given a LocalDate known to be a Monday */
        $date = LocalDate::of(year: 2026, month: 5, day: 25);

        /** @When retrieving the day of the week */
        $dayOfWeek = $date->dayOfWeek();

        /** @Then the day of the week is Monday */
        self::assertSame(DayOfWeek::Monday, $dayOfWeek);
    }

    public function testMinusDaysWhenZeroThenReturnsSameDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting zero days */
        $shifted = $date->minusDays(days: 0);

        /** @Then the result is the same date */
        self::assertSame('2026-05-23', $shifted->toIso8601());
    }

    public function testOfWhenDayAboveMaxThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <1>, and day <32> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with day 32 */
        LocalDate::of(year: 2026, month: 1, day: 32);
    }

    public function testPlusDaysWhenPositiveThenShiftsForward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding 7 days */
        $shifted = $date->plusDays(days: 7);

        /** @Then the result is 7 days later */
        self::assertSame('2026-05-30', $shifted->toIso8601());
    }

    public function testPlusYearsWhenCommonDateThenShiftsYear(): void
    {
        /** @Given a LocalDate on a common day */
        $date = LocalDate::of(year: 2026, month: 5, day: 15);

        /** @When adding two years */
        $shifted = $date->plusYears(years: 2);

        /** @Then the year is shifted forward and the month and day are preserved */
        self::assertSame('2028-05-15', $shifted->toIso8601());
    }

    public function testFromStringThenRoundTripsThroughIso8601(): void
    {
        /** @Given a date string in ISO 8601 format */
        $value = '2026-05-23';

        /** @When parsing and then formatting */
        $iso = LocalDate::fromString(value: $value)->toIso8601();

        /** @Then the output is identical to the input */
        self::assertSame($value, $iso);
    }

    public function testMinusDaysWhenNegativeThenShiftsForward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting -3 days (equivalent to adding 3) */
        $shifted = $date->minusDays(days: -3);

        /** @Then the result is 3 days later */
        self::assertSame('2026-05-26', $shifted->toIso8601());
    }

    public function testMinusYearsWhenCommonDateThenShiftsYear(): void
    {
        /** @Given a LocalDate on a common day */
        $date = LocalDate::of(year: 2026, month: 5, day: 15);

        /** @When subtracting two years */
        $shifted = $date->minusYears(years: 2);

        /** @Then the year is shifted backward and the month and day are preserved */
        self::assertSame('2024-05-15', $shifted->toIso8601());
    }

    public function testOfWhenValidComponentsThenDateIsCreated(): void
    {
        /** @When creating a LocalDate from valid components */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the accessors reflect the given components */
        self::assertSame(23, $date->dayOfMonth());
        self::assertSame(5, $date->month());
        self::assertSame(2026, $date->year());
        self::assertSame('2026-05-23', $date->toIso8601());
    }

    public function testPlusDaysWhenNegativeThenShiftsBackward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding -3 days (equivalent to subtracting 3) */
        $shifted = $date->plusDays(days: -3);

        /** @Then the result is 3 days earlier */
        self::assertSame('2026-05-20', $shifted->toIso8601());
    }

    public function testTodayWhenUtcThenDateIsWithinCurrentDay(): void
    {
        /** @Given the current UTC date before calling today */
        $before = new DateTimeImmutable(datetime: 'now', timezone: new DateTimeZone('UTC'))->format('Y-m-d');

        /** @When getting today's local date in UTC */
        $today = LocalDate::today(zone: Timezone::utc());

        /** @And the current UTC date after calling today */
        $after = new DateTimeImmutable(datetime: 'now', timezone: new DateTimeZone('UTC'))->format('Y-m-d');

        /** @Then today's date falls within the before/after bracket */
        self::assertGreaterThanOrEqual($before, $today->toIso8601());
        self::assertLessThanOrEqual($after, $today->toIso8601());
    }

    public function testMinusDaysWhenPositiveThenShiftsBackward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting 7 days */
        $shifted = $date->minusDays(days: 7);

        /** @Then the result is 7 days earlier */
        self::assertSame('2026-05-16', $shifted->toIso8601());
    }

    public function testDayOfWeekWhenSaturdayThenReturnsSaturday(): void
    {
        /** @Given a LocalDate known to be a Saturday */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When retrieving the day of the week */
        $dayOfWeek = $date->dayOfWeek();

        /** @Then the day of the week is Saturday */
        self::assertSame(DayOfWeek::Saturday, $dayOfWeek);
    }

    public function testMinusMonthsWhenNegativeThenShiftsForward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 1, day: 15);

        /** @When subtracting -1 month (equivalent to adding 1) */
        $shifted = $date->minusMonths(months: -1);

        /** @Then the result is one month later */
        self::assertSame('2026-02-15', $shifted->toIso8601());
    }

    public function testOfWhenLeapDayOnLeapYearThenDateIsCreated(): void
    {
        /** @When creating a LocalDate for February 29 on a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @Then the date is created successfully */
        self::assertSame('2024-02-29', $date->toIso8601());
    }

    public function testOfWhenYearAboveLimitThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <10000>, month <1>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with year 10000 (above 4-digit limit) */
        LocalDate::of(year: 10000, month: 1, day: 1);
    }

    public function testPlusMonthsWhenNegativeThenShiftsBackward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 3, day: 15);

        /** @When adding -1 month (equivalent to subtracting 1) */
        $shifted = $date->plusMonths(months: -1);

        /** @Then the result is one month earlier */
        self::assertSame('2026-02-15', $shifted->toIso8601());
    }

    public function testOfWhenMonthAboveRangeThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <13>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with month 13 */
        LocalDate::of(year: 2026, month: 13, day: 1);
    }

    public function testFromStringWhenValidIso8601ThenDateIsCreated(): void
    {
        /** @When creating a LocalDate from a valid ISO 8601 date string */
        $date = LocalDate::fromString(value: '2026-05-23');

        /** @Then the accessors reflect the parsed date */
        self::assertSame(23, $date->dayOfMonth());
        self::assertSame(5, $date->month());
        self::assertSame(2026, $date->year());
        self::assertSame('2026-05-23', $date->toIso8601());
    }

    public function testPlusMonthsWhenZeroThenReturnsEquivalentDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding zero months */
        $shifted = $date->plusMonths(months: 0);

        /** @Then the result is an equivalent date */
        self::assertSame('2026-05-23', $shifted->toIso8601());
    }

    public function testPlusYearsWhenLandsOnMaximumYearThenSucceeds(): void
    {
        /** @Given a date one year below the maximum supported year */
        $date = LocalDate::of(year: 9998, month: 5, day: 15);

        /** @When adding one year to reach the maximum year */
        $shifted = $date->plusYears(years: 1);

        /** @Then the result lands on the maximum supported year */
        self::assertSame('9999-05-15', $shifted->toIso8601());
    }

    public function testPlusYearsWhenLandsOnMinimumYearThenSucceeds(): void
    {
        /** @Given a date one year above the minimum supported year */
        $date = LocalDate::of(year: 2, month: 5, day: 15);

        /** @When subtracting one year to reach the minimum year */
        $shifted = $date->plusYears(years: -1);

        /** @Then the result lands on the minimum supported year */
        self::assertSame('0001-05-15', $shifted->toIso8601());
    }

    public function testPlusYearsWhenLeapDayThenClampsToTwentyEight(): void
    {
        /** @Given the leap day of a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @When adding one year to a common year */
        $shifted = $date->plusYears(years: 1);

        /** @Then the day is clamped to the last day of February */
        self::assertSame('2025-02-28', $shifted->toIso8601());
    }

    public function testMinusYearsWhenLeapDayThenClampsToTwentyEight(): void
    {
        /** @Given the leap day of a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @When subtracting one year to a common year */
        $shifted = $date->minusYears(years: 1);

        /** @Then the day is clamped to the last day of February */
        self::assertSame('2023-02-28', $shifted->toIso8601());
    }

    public function testOfWhenInvalidDayForMonthThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <4>, and day <31> do not form a valid calendar date.');

        /** @When trying to create April 31 (April has 30 days) */
        LocalDate::of(year: 2026, month: 4, day: 31);
    }

    public function testOfWhenYearAtMaximumBoundaryThenDateIsCreated(): void
    {
        /** @When creating a LocalDate with year 9999 (maximum) */
        $date = LocalDate::of(year: 9999, month: 12, day: 31);

        /** @Then the date is created successfully */
        self::assertSame('9999-12-31', $date->toIso8601());
    }

    public function testOfWhenYearAtMinimumBoundaryThenDateIsCreated(): void
    {
        /** @When creating a LocalDate with year 1 (minimum) */
        $date = LocalDate::of(year: 1, month: 1, day: 1);

        /** @Then the date is created successfully */
        self::assertSame('0001-01-01', $date->toIso8601());
    }

    public function testPlusMonthsWhenTargetIsJuneThenClampsToThirty(): void
    {
        /** @Given the last day of May */
        $date = LocalDate::of(year: 2026, month: 5, day: 31);

        /** @When adding one month to reach June */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the last day of June */
        self::assertSame('2026-06-30', $shifted->toIso8601());
    }

    public function testInstantToLocalDateWhenUtcMidnightThenSameDate(): void
    {
        /** @Given an Instant at midnight UTC on a known date */
        $instant = Instant::fromString(value: '2026-05-23T00:00:00+00:00');

        /** @When projecting into UTC */
        $localDate = $instant->toLocalDate(zone: Timezone::utc());

        /** @Then the local date matches the UTC date */
        self::assertSame('2026-05-23', $localDate->toIso8601());
    }

    public function testPlusDaysWhenCrossesLeapDayThenIncludesLeapDay(): void
    {
        /** @Given February 28 of a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 28);

        /** @When adding 1 day */
        $shifted = $date->plusDays(days: 1);

        /** @Then the result is the leap day */
        self::assertSame('2024-02-29', $shifted->toIso8601());
    }

    public function testPlusDaysWhenCrossesYearBoundaryThenShiftsYear(): void
    {
        /** @Given the last day of the year */
        $date = LocalDate::of(year: 2026, month: 12, day: 31);

        /** @When adding 1 day */
        $shifted = $date->plusDays(days: 1);

        /** @Then the result is the first day of the following year */
        self::assertSame('2027-01-01', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenLandsOnMaximumMonthThenSucceeds(): void
    {
        /** @Given a date one month below the last month of the maximum year */
        $date = LocalDate::of(year: 9999, month: 11, day: 15);

        /** @When adding one month to reach the last supported month */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the result lands on the last month of the maximum year */
        self::assertSame('9999-12-15', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenLandsOnMinimumMonthThenSucceeds(): void
    {
        /** @Given a date one month above the first month of the minimum year */
        $date = LocalDate::of(year: 1, month: 2, day: 15);

        /** @When subtracting one month to reach the first supported month */
        $shifted = $date->plusMonths(months: -1);

        /** @Then the result lands on the first month of the minimum year */
        self::assertSame('0001-01-15', $shifted->toIso8601());
    }

    #[DataProvider('invalidStringsDataProvider')]
    public function testFromStringWhenInvalidValueThenInvalidLocalDate(string $value): void
    {
        /** @Then an exception indicating an invalid local date value should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $template = 'The value <%s> is not a valid local date.';
        $this->expectExceptionMessage(sprintf($template, $value));

        /** @When trying to create a LocalDate from the invalid string */
        LocalDate::fromString(value: $value);
    }

    public function testMinusDaysWhenCrossesYearBoundaryThenShiftsYear(): void
    {
        /** @Given the first day of the year */
        $date = LocalDate::of(year: 2027, month: 1, day: 1);

        /** @When subtracting 1 day */
        $shifted = $date->minusDays(days: 1);

        /** @Then the result is the last day of the previous year */
        self::assertSame('2026-12-31', $shifted->toIso8601());
    }

    public function testOfWhenLeapDayOnNonLeapYearThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <2>, and day <29> do not form a valid calendar date.');

        /** @When trying to create February 29 on a non-leap year */
        LocalDate::of(year: 2026, month: 2, day: 29);
    }

    public function testToIso8601WhenLeapDayThenReturnsFormattedString(): void
    {
        /** @Given a LocalDate for a leap day */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output is the leap day string */
        self::assertSame('2024-02-29', $iso);
    }

    public function testPlusDaysWhenCrossesMonthBoundaryThenShiftsMonth(): void
    {
        /** @Given a date near the end of May */
        $date = LocalDate::of(year: 2026, month: 5, day: 29);

        /** @When adding 3 days */
        $shifted = $date->plusDays(days: 3);

        /** @Then the result crosses into June */
        self::assertSame('2026-06-01', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenCrossesYearBoundaryThenShiftsYear(): void
    {
        /** @Given a date late in the year */
        $date = LocalDate::of(year: 2026, month: 11, day: 15);

        /** @When adding three months */
        $shifted = $date->plusMonths(months: 3);

        /** @Then the result crosses into the following year */
        self::assertSame('2027-02-15', $shifted->toIso8601());
    }

    public function testMinusDaysWhenCrossesMonthBoundaryThenShiftsMonth(): void
    {
        /** @Given the first day of June */
        $date = LocalDate::of(year: 2026, month: 6, day: 1);

        /** @When subtracting 3 days */
        $shifted = $date->minusDays(days: 3);

        /** @Then the result crosses back into May */
        self::assertSame('2026-05-29', $shifted->toIso8601());
    }

    public function testMinusDaysWhenNegativeThenMatchesPlusDaysPositive(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding positive days */
        $viaPlus = $date->plusDays(days: 5);

        /** @And subtracting negative days (same magnitude) */
        $viaMinus = $date->minusDays(days: -5);

        /** @Then both produce the same date */
        self::assertSame($viaPlus->toIso8601(), $viaMinus->toIso8601());
    }

    public function testPlusDaysWhenNegativeThenMatchesMinusDaysPositive(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting positive days */
        $viaMinus = $date->minusDays(days: 5);

        /** @And adding negative days (same magnitude) */
        $viaPlus = $date->plusDays(days: -5);

        /** @Then both produce the same date */
        self::assertSame($viaMinus->toIso8601(), $viaPlus->toIso8601());
    }

    public function testPlusMonthsWhenDayFitsTargetMonthThenPreservesDay(): void
    {
        /** @Given a LocalDate whose day exists in the target month */
        $date = LocalDate::of(year: 2026, month: 1, day: 15);

        /** @When adding one month */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is preserved without clamping */
        self::assertSame('2026-02-15', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenTargetIsNovemberThenClampsToThirty(): void
    {
        /** @Given the last day of October */
        $date = LocalDate::of(year: 2026, month: 10, day: 31);

        /** @When adding one month to reach November */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the last day of November */
        self::assertSame('2026-11-30', $shifted->toIso8601());
    }

    public function testToIso8601WhenValidDateThenReturnsFormattedString(): void
    {
        /** @Given a LocalDate for a known date */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output matches YYYY-MM-DD format */
        self::assertSame('2026-05-23', $iso);
    }

    public function testInstantToLocalDateWhenEastZoneIsAheadThenNextDate(): void
    {
        /** @Given an Instant at 23:30 UTC on 2026-02-17 */
        $instant = Instant::fromString(value: '2026-02-17T23:30:00+00:00');

        /** @When projecting into Asia/Tokyo (UTC+9) */
        $localDate = $instant->toLocalDate(zone: Timezone::from(identifier: 'Asia/Tokyo'));

        /** @Then the local date is the following day relative to UTC */
        self::assertSame('2026-02-18', $localDate->toIso8601());
    }

    public function testPlusMonthsWhenTargetIsSeptemberThenClampsToThirty(): void
    {
        /** @Given the last day of August */
        $date = LocalDate::of(year: 2026, month: 8, day: 31);

        /** @When adding one month to reach September */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the last day of September */
        self::assertSame('2026-09-30', $shifted->toIso8601());
    }

    public function testPlusYearsWhenBelowMinimumYearThenInvalidLocalDate(): void
    {
        /** @Given a date on the minimum supported year */
        $date = LocalDate::of(year: 1, month: 5, day: 15);

        /** @Then an exception indicating an out-of-range shift should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When subtracting one year crosses below the minimum year */
        $date->plusYears(years: -1);
    }

    public function testPlusYearsWhenExceedsMaximumYearThenInvalidLocalDate(): void
    {
        /** @Given a date on the maximum supported year */
        $date = LocalDate::of(year: 9999, month: 5, day: 15);

        /** @Then an exception indicating an out-of-range shift should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding one year crosses above the maximum year */
        $date->plusYears(years: 1);
    }

    public function testToIso8601WhenYearBoundaryThenReturnsFormattedString(): void
    {
        /** @Given a LocalDate for the first day of a new year */
        $date = LocalDate::of(year: 2027, month: 1, day: 1);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output reflects the new year */
        self::assertSame('2027-01-01', $iso);
    }

    public function testPlusMonthsWhenTargetIsLeapFebruaryThenClampsToLeapDay(): void
    {
        /** @Given the last day of January in a leap year */
        $date = LocalDate::of(year: 2028, month: 1, day: 31);

        /** @When adding one month to reach February of the leap year */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the leap day */
        self::assertSame('2028-02-29', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenTargetMonthIsShorterThenClampsToLastDay(): void
    {
        /** @Given the last day of a 31-day month */
        $date = LocalDate::of(year: 2026, month: 1, day: 31);

        /** @When adding one month to reach a 28-day month */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the last day of the target month */
        self::assertSame('2026-02-28', $shifted->toIso8601());
    }

    public function testPlusYearsWhenArgumentIsIntegerMaxThenInvalidLocalDate(): void
    {
        /** @Given a date at a known moment */
        $date = LocalDate::of(year: 2026, month: 5, day: 15);

        /** @Then an out-of-range shift is raised instead of an arithmetic overflow */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding the maximum integer number of years */
        $date->plusYears(years: PHP_INT_MAX);
    }

    public function testMinusMonthsWhenTargetMonthIsShorterThenClampsToLastDay(): void
    {
        /** @Given the last day of a 31-day month */
        $date = LocalDate::of(year: 2026, month: 3, day: 31);

        /** @When subtracting one month to reach a 28-day month */
        $shifted = $date->minusMonths(months: 1);

        /** @Then the day is clamped to the last day of the target month */
        self::assertSame('2026-02-28', $shifted->toIso8601());
    }

    public function testPlusDaysWhenFollowedByMinusDaysThenReturnsOriginalDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding 10 days and then subtracting 10 days */
        $shifted = $date->plusDays(days: 10)->minusDays(days: 10);

        /** @Then the result is the original date */
        self::assertSame($date->toIso8601(), $shifted->toIso8601());
    }

    public function testPlusMonthsWhenArgumentIsIntegerMaxThenInvalidLocalDate(): void
    {
        /** @Given a date at a known moment */
        $date = LocalDate::of(year: 2026, month: 5, day: 15);

        /** @Then an out-of-range shift is raised instead of an arithmetic overflow */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding the maximum integer number of months */
        $date->plusMonths(months: PHP_INT_MAX);
    }

    public function testInstantToLocalDateWhenInUtcThenRoundTripsThroughIso8601(): void
    {
        /** @Given an Instant at a known UTC moment */
        $instant = Instant::fromString(value: '2026-05-23T12:00:00+00:00');

        /** @When projecting into UTC */
        $localDate = $instant->toLocalDate(zone: Timezone::utc());

        /** @Then the ISO 8601 string can be parsed back to the same date */
        self::assertSame($localDate->toIso8601(), LocalDate::fromString(value: $localDate->toIso8601())->toIso8601());
    }

    public function testPlusMonthsWhenFollowedByMinusMonthsThenIsNotAssociative(): void
    {
        /** @Given the last day of a 31-day month */
        $date = LocalDate::of(year: 2026, month: 1, day: 31);

        /** @When adding one month and then subtracting one month */
        $shifted = $date->plusMonths(months: 1)->minusMonths(months: 1);

        /** @Then the clamped day is not restored, so the operation is not associative */
        self::assertSame('2026-01-28', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenTargetMonthHasThirtyDaysThenClampsToThirty(): void
    {
        /** @Given the last day of a 31-day month */
        $date = LocalDate::of(year: 2026, month: 1, day: 31);

        /** @When adding three months to reach a 30-day month */
        $shifted = $date->plusMonths(months: 3);

        /** @Then the day is clamped to the last day of the target month */
        self::assertSame('2026-04-30', $shifted->toIso8601());
    }

    public function testMinusMonthsWhenResultBelowMinimumYearThenInvalidLocalDate(): void
    {
        /** @Given the first day of the minimum supported year */
        $date = LocalDate::of(year: 1, month: 1, day: 1);

        /** @Then an exception indicating an out-of-range shift should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When subtracting one month crosses below the minimum year */
        $date->minusMonths(months: 1);
    }

    public function testPlusMonthsWhenResultExceedsMaximumYearThenInvalidLocalDate(): void
    {
        /** @Given the last month of the maximum supported year */
        $date = LocalDate::of(year: 9999, month: 12, day: 1);

        /** @Then an exception indicating an out-of-range shift should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('The shifted date falls outside the supported range 0001 to 9999.');

        /** @When adding one month crosses above the maximum year */
        $date->plusMonths(months: 1);
    }

    public function testPlusMonthsWhenTargetIsCommonCenturyThenClampsToTwentyEight(): void
    {
        /** @Given the last day of January in a common century year */
        $date = LocalDate::of(year: 1900, month: 1, day: 31);

        /** @When adding one month to reach February of the non-leap century year */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the last day of February */
        self::assertSame('1900-02-28', $shifted->toIso8601());
    }

    public function testPlusMonthsWhenTargetIsFourHundredYearLeapThenClampsToLeapDay(): void
    {
        /** @Given the last day of January in a year divisible by four hundred */
        $date = LocalDate::of(year: 2000, month: 1, day: 31);

        /** @When adding one month to reach February of the leap century year */
        $shifted = $date->plusMonths(months: 1);

        /** @Then the day is clamped to the leap day */
        self::assertSame('2000-02-29', $shifted->toIso8601());
    }

    public function testInstantToLocalDateWhenJustPastMidnightUtcInWestZoneThenPreviousDate(): void
    {
        /** @Given an Instant at 00:30 UTC on 2026-02-18 */
        $instant = Instant::fromString(value: '2026-02-18T00:30:00+00:00');

        /** @When projecting into America/New_York (UTC-5 in February) */
        $localDate = $instant->toLocalDate(zone: Timezone::from(identifier: 'America/New_York'));

        /** @Then the local date is the previous day relative to UTC */
        self::assertSame('2026-02-17', $localDate->toIso8601());
    }

    public static function invalidStringsDataProvider(): array
    {
        return [
            'Empty string'                     => ['value' => ''],
            'Plain text'                       => ['value' => 'garbage'],
            'Invalid month'                    => ['value' => '2026-13-01'],
            'Invalid day for month'            => ['value' => '2026-02-30'],
            'Wrong separator'                  => ['value' => '2026/05/23'],
            'Missing separators'               => ['value' => '20260523'],
            'Single-digit month'               => ['value' => '2026-5-23'],
            'Single-digit day'                 => ['value' => '2026-05-3'],
            'Full datetime string'             => ['value' => '2026-05-23T00:00:00+00:00'],
            'Date with trailing whitespace'    => ['value' => '2026-05-23 '],
            'Date with leading whitespace'     => ['value' => ' 2026-05-23']
        ];
    }
}
