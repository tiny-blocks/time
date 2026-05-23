<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\DayOfWeek;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\LocalDate;
use TinyBlocks\Time\Timezone;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;

final class LocalDateTest extends TestCase
{
    public function testLocalDateOfWhenValidComponentsThenDateIsCreated(): void
    {
        /** @When creating a LocalDate from valid components */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the accessors reflect the given components */
        self::assertSame(23, $date->dayOfMonth());
        self::assertSame(5, $date->month());
        self::assertSame(2026, $date->year());
        self::assertSame('2026-05-23', $date->toIso8601());
    }

    public function testLocalDateOfWhenYearAtMinimumBoundaryThenDateIsCreated(): void
    {
        /** @When creating a LocalDate with year 1 (minimum) */
        $date = LocalDate::of(year: 1, month: 1, day: 1);

        /** @Then the date is created successfully */
        self::assertSame('0001-01-01', $date->toIso8601());
    }

    public function testLocalDateOfWhenYearAtMaximumBoundaryThenDateIsCreated(): void
    {
        /** @When creating a LocalDate with year 9999 (maximum) */
        $date = LocalDate::of(year: 9999, month: 12, day: 31);

        /** @Then the date is created successfully */
        self::assertSame('9999-12-31', $date->toIso8601());
    }

    public function testLocalDateOfWhenLeapDayOnLeapYearThenDateIsCreated(): void
    {
        /** @When creating a LocalDate for February 29 on a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @Then the date is created successfully */
        self::assertSame('2024-02-29', $date->toIso8601());
    }

    public function testLocalDateOfWhenDayZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <1>, and day <0> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with day zero */
        LocalDate::of(year: 2026, month: 1, day: 0);
    }

    public function testLocalDateOfWhenYearZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <0>, month <1>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with year zero */
        LocalDate::of(year: 0, month: 1, day: 1);
    }

    public function testLocalDateOfWhenDayAboveMaxThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <1>, and day <32> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with day 32 */
        LocalDate::of(year: 2026, month: 1, day: 32);
    }

    public function testLocalDateOfWhenMonthZeroThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <0>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with month zero */
        LocalDate::of(year: 2026, month: 0, day: 1);
    }

    public function testLocalDateOfWhenMonthAboveRangeThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <13>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with month 13 */
        LocalDate::of(year: 2026, month: 13, day: 1);
    }

    public function testLocalDateOfWhenLeapDayOnNonLeapYearThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <2>, and day <29> do not form a valid calendar date.');

        /** @When trying to create February 29 on a non-leap year */
        LocalDate::of(year: 2026, month: 2, day: 29);
    }

    public function testLocalDateOfWhenInvalidDayForMonthThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <2026>, month <4>, and day <31> do not form a valid calendar date.');

        /** @When trying to create April 31 (April has 30 days) */
        LocalDate::of(year: 2026, month: 4, day: 31);
    }

    public function testLocalDateOfWhenYearAboveLimitThenInvalidLocalDate(): void
    {
        /** @Then an exception indicating invalid components should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage('Year <10000>, month <1>, and day <1> do not form a valid calendar date.');

        /** @When trying to create a LocalDate with year 10000 (above 4-digit limit) */
        LocalDate::of(year: 10000, month: 1, day: 1);
    }

    public function testLocalDateFromStringWhenValidIso8601ThenDateIsCreated(): void
    {
        /** @When creating a LocalDate from a valid ISO 8601 date string */
        $date = LocalDate::fromString(value: '2026-05-23');

        /** @Then the accessors reflect the parsed date */
        self::assertSame(23, $date->dayOfMonth());
        self::assertSame(5, $date->month());
        self::assertSame(2026, $date->year());
        self::assertSame('2026-05-23', $date->toIso8601());
    }

    #[DataProvider('invalidStringsDataProvider')]
    public function testLocalDateFromStringWhenInvalidValueThenInvalidLocalDate(string $value): void
    {
        /** @Then an exception indicating an invalid local date value should be thrown */
        $this->expectException(InvalidLocalDate::class);
        $this->expectExceptionMessage(sprintf('The value <%s> is not a valid local date.', $value));

        /** @When trying to create a LocalDate from the invalid string */
        LocalDate::fromString(value: $value);
    }

    public function testLocalDateTodayWhenUtcThenDateIsWithinCurrentDay(): void
    {
        /** @Given the current UTC date before calling today */
        $before = (new DateTimeImmutable(datetime: 'now', timezone: new DateTimeZone('UTC')))->format('Y-m-d');

        /** @When getting today's local date in UTC */
        $today = LocalDate::today(zone: Timezone::utc());

        /** @And the current UTC date after calling today */
        $after = (new DateTimeImmutable(datetime: 'now', timezone: new DateTimeZone('UTC')))->format('Y-m-d');

        /** @Then today's date falls within the before/after bracket */
        self::assertGreaterThanOrEqual($before, $today->toIso8601());
        self::assertLessThanOrEqual($after, $today->toIso8601());
    }

    public function testLocalDateDayOfWeekWhenKnownDateThenReturnsCorrectDay(): void
    {
        /** @Given a LocalDate known to be a Saturday */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When retrieving the day of the week */
        $dayOfWeek = $date->dayOfWeek();

        /** @Then the day of the week is Saturday */
        self::assertSame(DayOfWeek::Saturday, $dayOfWeek);
    }

    public function testLocalDateDayOfWeekWhenMondayThenReturnsMonday(): void
    {
        /** @Given a LocalDate known to be a Monday */
        $date = LocalDate::of(year: 2026, month: 5, day: 25);

        /** @When retrieving the day of the week */
        $dayOfWeek = $date->dayOfWeek();

        /** @Then the day of the week is Monday */
        self::assertSame(DayOfWeek::Monday, $dayOfWeek);
    }

    public function testLocalDateToIso8601WhenValidDateThenReturnsCorrectString(): void
    {
        /** @Given a LocalDate for a known date */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output matches YYYY-MM-DD format */
        self::assertSame('2026-05-23', $iso);
    }

    public function testLocalDateToIso8601WhenLeapDayThenReturnsCorrectString(): void
    {
        /** @Given a LocalDate for a leap day */
        $date = LocalDate::of(year: 2024, month: 2, day: 29);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output is the leap day string */
        self::assertSame('2024-02-29', $iso);
    }

    public function testLocalDateToIso8601WhenYearBoundaryThenReturnsCorrectString(): void
    {
        /** @Given a LocalDate for the first day of a new year */
        $date = LocalDate::of(year: 2027, month: 1, day: 1);

        /** @When converting to ISO 8601 */
        $iso = $date->toIso8601();

        /** @Then the output reflects the new year */
        self::assertSame('2027-01-01', $iso);
    }

    public function testLocalDateFromStringRoundTripsWithToIso8601(): void
    {
        /** @Given a date string in ISO 8601 format */
        $value = '2026-05-23';

        /** @When parsing and then formatting */
        $iso = LocalDate::fromString(value: $value)->toIso8601();

        /** @Then the output is identical to the input */
        self::assertSame($value, $iso);
    }

    public function testLocalDateIsAfterWhenAfterThenTrue(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the later date is after the earlier */
        self::assertTrue($later->isAfter(other: $earlier));
    }

    public function testLocalDateIsAfterWhenEqualThenFalse(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then isAfter returns false for equal dates */
        self::assertFalse($first->isAfter(other: $second));
    }

    public function testLocalDateIsAfterWhenBeforeThenFalse(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @Then the earlier date is not after the later */
        self::assertFalse($earlier->isAfter(other: $later));
    }

    public function testLocalDateIsBeforeWhenBeforeThenTrue(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @Then the earlier date is before the later */
        self::assertTrue($earlier->isBefore(other: $later));
    }

    public function testLocalDateIsBeforeWhenEqualThenFalse(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then isBefore returns false for equal dates */
        self::assertFalse($first->isBefore(other: $second));
    }

    public function testLocalDateIsBeforeWhenAfterThenFalse(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the later date is not before the earlier */
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testLocalDateIsAfterOrEqualWhenAfterThenTrue(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the later date is after or equal to the earlier */
        self::assertTrue($later->isAfterOrEqual(other: $earlier));
    }

    public function testLocalDateIsAfterOrEqualWhenEqualThenTrue(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then isAfterOrEqual returns true for equal dates */
        self::assertTrue($first->isAfterOrEqual(other: $second));
    }

    public function testLocalDateIsAfterOrEqualWhenBeforeThenFalse(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @Then the earlier date is not after or equal to the later */
        self::assertFalse($earlier->isAfterOrEqual(other: $later));
    }

    public function testLocalDateIsBeforeOrEqualWhenBeforeThenTrue(): void
    {
        /** @Given an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @Then the earlier date is before or equal to the later */
        self::assertTrue($earlier->isBeforeOrEqual(other: $later));
    }

    public function testLocalDateIsBeforeOrEqualWhenEqualThenTrue(): void
    {
        /** @Given two dates representing the same day */
        $first = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @And another date representing the same day */
        $second = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then isBeforeOrEqual returns true for equal dates */
        self::assertTrue($first->isBeforeOrEqual(other: $second));
    }

    public function testLocalDateIsBeforeOrEqualWhenAfterThenFalse(): void
    {
        /** @Given a later date */
        $later = LocalDate::of(year: 2026, month: 5, day: 24);

        /** @And an earlier date */
        $earlier = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @Then the later date is not before or equal to the earlier */
        self::assertFalse($later->isBeforeOrEqual(other: $earlier));
    }

    public function testLocalDatePlusDaysWhenPositiveThenShiftsForward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding 7 days */
        $result = $date->plusDays(days: 7);

        /** @Then the result is 7 days later */
        self::assertSame('2026-05-30', $result->toIso8601());
    }

    public function testLocalDatePlusDaysWhenNegativeThenShiftsBackward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding -3 days (equivalent to subtracting 3) */
        $result = $date->plusDays(days: -3);

        /** @Then the result is 3 days earlier */
        self::assertSame('2026-05-20', $result->toIso8601());
    }

    public function testLocalDatePlusDaysWhenZeroThenReturnsSameDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding zero days */
        $result = $date->plusDays(days: 0);

        /** @Then the result is the same date */
        self::assertSame('2026-05-23', $result->toIso8601());
    }

    public function testLocalDatePlusDaysWhenCrossesMonthBoundaryThenShiftsMonth(): void
    {
        /** @Given a date near the end of May */
        $date = LocalDate::of(year: 2026, month: 5, day: 29);

        /** @When adding 3 days */
        $result = $date->plusDays(days: 3);

        /** @Then the result crosses into June */
        self::assertSame('2026-06-01', $result->toIso8601());
    }

    public function testLocalDatePlusDaysWhenCrossesYearBoundaryThenShiftsYear(): void
    {
        /** @Given the last day of the year */
        $date = LocalDate::of(year: 2026, month: 12, day: 31);

        /** @When adding 1 day */
        $result = $date->plusDays(days: 1);

        /** @Then the result is the first day of the following year */
        self::assertSame('2027-01-01', $result->toIso8601());
    }

    public function testLocalDatePlusDaysWhenCrossesLeapDayThenCountsCorrectly(): void
    {
        /** @Given February 28 of a leap year */
        $date = LocalDate::of(year: 2024, month: 2, day: 28);

        /** @When adding 1 day */
        $result = $date->plusDays(days: 1);

        /** @Then the result is the leap day */
        self::assertSame('2024-02-29', $result->toIso8601());
    }

    public function testLocalDateMinusDaysWhenPositiveThenShiftsBackward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting 7 days */
        $result = $date->minusDays(days: 7);

        /** @Then the result is 7 days earlier */
        self::assertSame('2026-05-16', $result->toIso8601());
    }

    public function testLocalDateMinusDaysWhenNegativeThenShiftsForward(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting -3 days (equivalent to adding 3) */
        $result = $date->minusDays(days: -3);

        /** @Then the result is 3 days later */
        self::assertSame('2026-05-26', $result->toIso8601());
    }

    public function testLocalDateMinusDaysWhenZeroThenReturnsSameDate(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When subtracting zero days */
        $result = $date->minusDays(days: 0);

        /** @Then the result is the same date */
        self::assertSame('2026-05-23', $result->toIso8601());
    }

    public function testLocalDateMinusDaysWhenCrossesMonthBoundaryThenShiftsMonth(): void
    {
        /** @Given the first day of June */
        $date = LocalDate::of(year: 2026, month: 6, day: 1);

        /** @When subtracting 3 days */
        $result = $date->minusDays(days: 3);

        /** @Then the result crosses back into May */
        self::assertSame('2026-05-29', $result->toIso8601());
    }

    public function testLocalDateMinusDaysWhenCrossesYearBoundaryThenShiftsYear(): void
    {
        /** @Given the first day of the year */
        $date = LocalDate::of(year: 2027, month: 1, day: 1);

        /** @When subtracting 1 day */
        $result = $date->minusDays(days: 1);

        /** @Then the result is the last day of the previous year */
        self::assertSame('2026-12-31', $result->toIso8601());
    }

    public function testLocalDatePlusDaysAndMinusDaysAreInverse(): void
    {
        /** @Given a LocalDate */
        $date = LocalDate::of(year: 2026, month: 5, day: 23);

        /** @When adding 10 days and then subtracting 10 days */
        $result = $date->plusDays(days: 10)->minusDays(days: 10);

        /** @Then the result is the original date */
        self::assertSame($date->toIso8601(), $result->toIso8601());
    }

    public function testLocalDateMinusDaysNegativeEquivalentToPlusDaysPositive(): void
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

    public function testLocalDatePlusDaysNegativeEquivalentToMinusDaysPositive(): void
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

    public function testInstantToLocalDateWhenUtcMidnightThenSameDate(): void
    {
        /** @Given an Instant at midnight UTC on a known date */
        $instant = Instant::fromString(value: '2026-05-23T00:00:00+00:00');

        /** @When projecting into UTC */
        $localDate = $instant->toLocalDate(zone: Timezone::utc());

        /** @Then the local date matches the UTC date */
        self::assertSame('2026-05-23', $localDate->toIso8601());
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

    public function testInstantToLocalDateWhenEastZoneIsAheadThenNextDate(): void
    {
        /** @Given an Instant at 23:30 UTC on 2026-02-17 */
        $instant = Instant::fromString(value: '2026-02-17T23:30:00+00:00');

        /** @When projecting into Asia/Tokyo (UTC+9) */
        $localDate = $instant->toLocalDate(zone: Timezone::from(identifier: 'Asia/Tokyo'));

        /** @Then the local date is the following day relative to UTC */
        self::assertSame('2026-02-18', $localDate->toIso8601());
    }

    public function testInstantToLocalDateWhenUtcAndLocalDateRoundTrips(): void
    {
        /** @Given an Instant at a known UTC moment */
        $instant = Instant::fromString(value: '2026-05-23T12:00:00+00:00');

        /** @When projecting into UTC */
        $localDate = $instant->toLocalDate(zone: Timezone::utc());

        /** @Then the ISO 8601 string can be parsed back to the same date */
        self::assertSame($localDate->toIso8601(), LocalDate::fromString(value: $localDate->toIso8601())->toIso8601());
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
