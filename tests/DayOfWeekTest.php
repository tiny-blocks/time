<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\Attributes\DataProvider;
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
        self::assertNotSame(DayOfWeek::Monday->isWeekday(), DayOfWeek::Monday->isWeekend());
        self::assertNotSame(DayOfWeek::Tuesday->isWeekday(), DayOfWeek::Tuesday->isWeekend());
        self::assertNotSame(DayOfWeek::Wednesday->isWeekday(), DayOfWeek::Wednesday->isWeekend());
        self::assertNotSame(DayOfWeek::Thursday->isWeekday(), DayOfWeek::Thursday->isWeekend());
        self::assertNotSame(DayOfWeek::Friday->isWeekday(), DayOfWeek::Friday->isWeekend());
        self::assertNotSame(DayOfWeek::Saturday->isWeekday(), DayOfWeek::Saturday->isWeekend());
        self::assertNotSame(DayOfWeek::Sunday->isWeekday(), DayOfWeek::Sunday->isWeekend());
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

    #[DataProvider('sameDayDistanceDataProvider')]
    public function testDayOfWeekDistanceToSameDayReturnsZero(DayOfWeek $day): void
    {
        /** @Given the same day of the week */
        /** @Then the distance to itself should be zero */
        self::assertSame(0, $day->distanceTo(other: $day));
    }

    #[DataProvider('forwardDistanceDataProvider')]
    public function testDayOfWeekDistanceToForward(DayOfWeek $from, DayOfWeek $to, int $expectedDistance): void
    {
        /** @Given a starting day and a target day */
        /** @Then the forward distance should match the expected value */
        self::assertSame($expectedDistance, $from->distanceTo(other: $to));
    }

    #[DataProvider('wrapAroundDistanceDataProvider')]
    public function testDayOfWeekDistanceToWrapsAroundWeek(DayOfWeek $from, DayOfWeek $to, int $expectedDistance): void
    {
        /** @Given a starting day that is after the target day in the week */
        /** @Then the distance should wrap forward through the end of the week */
        self::assertSame($expectedDistance, $from->distanceTo(other: $to));
    }

    #[DataProvider('asymmetricDistanceDataProvider')]
    public function testDayOfWeekDistanceToIsNotSymmetric(
        DayOfWeek $from,
        DayOfWeek $to,
        int $expectedForward,
        int $expectedBackward
    ): void {
        /** @Given two distinct days of the week */
        /** @Then the forward and backward distances should differ */
        self::assertSame($expectedForward, $from->distanceTo(other: $to));
        self::assertSame($expectedBackward, $to->distanceTo(other: $from));

        /** @And together they should complete a full week */
        self::assertSame(7, $expectedForward + $expectedBackward);
    }

    #[DataProvider('allPairsDistanceDataProvider')]
    public function testDayOfWeekDistanceToNeverExceedsSix(DayOfWeek $from, DayOfWeek $to): void
    {
        /** @Given any pair of days */
        $distance = $from->distanceTo(other: $to);

        /** @Then the distance should be in the range [0, 6] */
        self::assertGreaterThanOrEqual(0, $distance);
        self::assertLessThanOrEqual(6, $distance);
    }

    public static function sameDayDistanceDataProvider(): array
    {
        return [
            'Monday to Monday'       => ['day' => DayOfWeek::Monday],
            'Tuesday to Tuesday'     => ['day' => DayOfWeek::Tuesday],
            'Wednesday to Wednesday' => ['day' => DayOfWeek::Wednesday],
            'Thursday to Thursday'   => ['day' => DayOfWeek::Thursday],
            'Friday to Friday'       => ['day' => DayOfWeek::Friday],
            'Saturday to Saturday'   => ['day' => DayOfWeek::Saturday],
            'Sunday to Sunday'       => ['day' => DayOfWeek::Sunday]
        ];
    }

    public static function forwardDistanceDataProvider(): array
    {
        return [
            'Monday to Tuesday'     => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Tuesday,
                'expectedDistance' => 1
            ],
            'Monday to Wednesday'   => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Wednesday,
                'expectedDistance' => 2
            ],
            'Monday to Thursday'    => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Thursday,
                'expectedDistance' => 3
            ],
            'Monday to Friday'      => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Friday,
                'expectedDistance' => 4
            ],
            'Monday to Saturday'    => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Saturday,
                'expectedDistance' => 5
            ],
            'Monday to Sunday'      => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Sunday,
                'expectedDistance' => 6
            ],
            'Tuesday to Thursday'   => [
                'from'             => DayOfWeek::Tuesday,
                'to'               => DayOfWeek::Thursday,
                'expectedDistance' => 2
            ],
            'Wednesday to Saturday' => [
                'from'             => DayOfWeek::Wednesday,
                'to'               => DayOfWeek::Saturday,
                'expectedDistance' => 3
            ]
        ];
    }

    public static function wrapAroundDistanceDataProvider(): array
    {
        return [
            'Friday to Monday'     => ['from' => DayOfWeek::Friday, 'to' => DayOfWeek::Monday, 'expectedDistance' => 3],
            'Saturday to Monday'   => [
                'from'             => DayOfWeek::Saturday,
                'to'               => DayOfWeek::Monday,
                'expectedDistance' => 2
            ],
            'Sunday to Monday'     => ['from' => DayOfWeek::Sunday, 'to' => DayOfWeek::Monday, 'expectedDistance' => 1],
            'Wednesday to Monday'  => [
                'from'             => DayOfWeek::Wednesday,
                'to'               => DayOfWeek::Monday,
                'expectedDistance' => 5
            ],
            'Saturday to Thursday' => [
                'from'             => DayOfWeek::Saturday,
                'to'               => DayOfWeek::Thursday,
                'expectedDistance' => 5
            ],
            'Thursday to Tuesday'  => [
                'from'             => DayOfWeek::Thursday,
                'to'               => DayOfWeek::Tuesday,
                'expectedDistance' => 5
            ],
            'Sunday to Wednesday'  => [
                'from'             => DayOfWeek::Sunday,
                'to'               => DayOfWeek::Wednesday,
                'expectedDistance' => 3
            ]
        ];
    }

    public static function asymmetricDistanceDataProvider(): array
    {
        return [
            'Monday and Wednesday' => [
                'from'             => DayOfWeek::Monday,
                'to'               => DayOfWeek::Wednesday,
                'expectedForward'  => 2,
                'expectedBackward' => 5
            ],
            'Tuesday and Friday'   => [
                'from'             => DayOfWeek::Tuesday,
                'to'               => DayOfWeek::Friday,
                'expectedForward'  => 3,
                'expectedBackward' => 4
            ],
            'Thursday and Sunday'  => [
                'from'             => DayOfWeek::Thursday,
                'to'               => DayOfWeek::Sunday,
                'expectedForward'  => 3,
                'expectedBackward' => 4
            ],
            'Saturday and Monday'  => [
                'from'             => DayOfWeek::Saturday,
                'to'               => DayOfWeek::Monday,
                'expectedForward'  => 2,
                'expectedBackward' => 5
            ]
        ];
    }

    public static function allPairsDistanceDataProvider(): array
    {
        $pairs = [];

        $days = DayOfWeek::cases();

        foreach ($days as $from) {
            foreach ($days as $to) {
                $label = sprintf('%s to %s', $from->name, $to->name);
                $pairs[$label] = ['from' => $from, 'to' => $to];
            }
        }

        return $pairs;
    }
}
