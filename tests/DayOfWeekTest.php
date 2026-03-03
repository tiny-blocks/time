<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\DayOfWeek;
use TinyBlocks\Time\Instant;

final class DayOfWeekTest extends TestCase
{
    public function testDayOfWeekMondayIsWeekday(): void
    {
        /** @Then Monday should be a weekday */
        self::assertTrue(DayOfWeek::Monday->isWeekday());
        self::assertFalse(DayOfWeek::Monday->isWeekend());
    }

    public function testDayOfWeekFridayIsWeekday(): void
    {
        /** @Then Friday should be a weekday */
        self::assertTrue(DayOfWeek::Friday->isWeekday());
        self::assertFalse(DayOfWeek::Friday->isWeekend());
    }

    public function testDayOfWeekSaturdayIsWeekend(): void
    {
        /** @Then Saturday should be a weekend day */
        self::assertTrue(DayOfWeek::Saturday->isWeekend());
        self::assertFalse(DayOfWeek::Saturday->isWeekday());
    }

    public function testDayOfWeekSundayIsWeekend(): void
    {
        /** @Then Sunday should be a weekend day */
        self::assertTrue(DayOfWeek::Sunday->isWeekend());
        self::assertFalse(DayOfWeek::Sunday->isWeekday());
    }

    public function testDayOfWeekAllDaysHaveCorrectIsoValues(): void
    {
        /** @Then each day should map to its ISO 8601 numeric value */
        self::assertSame(1, DayOfWeek::Monday->value);
        self::assertSame(2, DayOfWeek::Tuesday->value);
        self::assertSame(3, DayOfWeek::Wednesday->value);
        self::assertSame(4, DayOfWeek::Thursday->value);
        self::assertSame(5, DayOfWeek::Friday->value);
        self::assertSame(6, DayOfWeek::Saturday->value);
        self::assertSame(7, DayOfWeek::Sunday->value);
    }

    public function testDayOfWeekFromInstantOnMonday(): void
    {
        /** @Given an Instant on Monday 2026-02-16 */
        $instant = Instant::fromString(value: '2026-02-16T10:00:00+00:00');

        /** @Then the day should be Monday */
        self::assertSame(DayOfWeek::Monday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnTuesday(): void
    {
        /** @Given an Instant on Tuesday 2026-02-17 */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the day should be Tuesday */
        self::assertSame(DayOfWeek::Tuesday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnWednesday(): void
    {
        /** @Given an Instant on Wednesday 2026-02-18 */
        $instant = Instant::fromString(value: '2026-02-18T14:30:00+00:00');

        /** @Then the day should be Wednesday */
        self::assertSame(DayOfWeek::Wednesday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnThursday(): void
    {
        /** @Given an Instant at midnight on Thursday 2026-02-19 */
        $instant = Instant::fromString(value: '2026-02-19T00:00:00+00:00');

        /** @Then the day should be Thursday */
        self::assertSame(DayOfWeek::Thursday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnFriday(): void
    {
        /** @Given an Instant on Friday 2026-02-20 */
        $instant = Instant::fromString(value: '2026-02-20T17:00:00+00:00');

        /** @Then the day should be Friday */
        self::assertSame(DayOfWeek::Friday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnSaturday(): void
    {
        /** @Given an Instant on Saturday 2026-02-21 */
        $instant = Instant::fromString(value: '2026-02-21T08:00:00+00:00');

        /** @Then the day should be Saturday */
        self::assertSame(DayOfWeek::Saturday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekFromInstantOnSunday(): void
    {
        /** @Given an Instant on Sunday 2026-02-22 */
        $instant = Instant::fromString(value: '2026-02-22T23:59:59+00:00');

        /** @Then the day should be Sunday */
        self::assertSame(DayOfWeek::Sunday, DayOfWeek::fromInstant(instant: $instant));
    }

    public function testDayOfWeekWeekdayAndWeekendAreMutuallyExclusive(): void
    {
        /** @Then every day should be exactly one of weekday or weekend */
        foreach (DayOfWeek::cases() as $day) {
            self::assertNotSame($day->isWeekday(), $day->isWeekend());
        }
    }

    public function testDayOfWeekExactlyFiveWeekdays(): void
    {
        /** @Then there should be exactly 5 weekdays */
        $weekdays = array_filter(
            DayOfWeek::cases(),
            static fn(DayOfWeek $day): bool => $day->isWeekday()
        );

        self::assertCount(5, $weekdays);
    }

    public function testDayOfWeekExactlyTwoWeekendDays(): void
    {
        /** @Then there should be exactly 2 weekend days */
        $weekends = array_filter(
            DayOfWeek::cases(),
            static fn(DayOfWeek $day): bool => $day->isWeekend()
        );

        self::assertCount(2, $weekends);
    }
}
