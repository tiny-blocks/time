<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\DayOfWeek;
use TinyBlocks\Time\Instant;

final class DayOfWeekTest extends TestCase
{
    public function testDayOfWeekMondayIsWeekday(): void
    {
        /** @Given Monday as the day of the week */
        $day = DayOfWeek::Monday;

        /** @When checking if it is a weekday */
        $isWeekday = $day->isWeekday();

        /** @Then it should be a weekday */
        self::assertTrue($isWeekday);

        /** @And it should not be a weekend day */
        self::assertFalse($day->isWeekend());
    }

    public function testDayOfWeekFridayIsWeekday(): void
    {
        /** @Given Friday as the day of the week */
        $day = DayOfWeek::Friday;

        /** @When checking if it is a weekday */
        $isWeekday = $day->isWeekday();

        /** @Then it should be a weekday */
        self::assertTrue($isWeekday);

        /** @And it should not be a weekend day */
        self::assertFalse($day->isWeekend());
    }

    public function testDayOfWeekSaturdayIsWeekend(): void
    {
        /** @Given Saturday as the day of the week */
        $day = DayOfWeek::Saturday;

        /** @When checking if it is a weekend day */
        $isWeekend = $day->isWeekend();

        /** @Then it should be a weekend day */
        self::assertTrue($isWeekend);

        /** @And it should not be a weekday */
        self::assertFalse($day->isWeekday());
    }

    public function testDayOfWeekSundayIsWeekend(): void
    {
        /** @Given Sunday as the day of the week */
        $day = DayOfWeek::Sunday;

        /** @When checking if it is a weekend day */
        $isWeekend = $day->isWeekend();

        /** @Then it should be a weekend day */
        self::assertTrue($isWeekend);

        /** @And it should not be a weekday */
        self::assertFalse($day->isWeekday());
    }

    public function testDayOfWeekAllDaysHaveCorrectIsoValues(): void
    {
        /** @Given all days of the week in ISO 8601 order */
        $days = DayOfWeek::cases();

        /** @When inspecting their backing values */
        $values = array_map(static fn(DayOfWeek $day): int => $day->value, $days);

        /** @Then each day should map to its ISO 8601 numeric value */
        self::assertSame([1, 2, 3, 4, 5, 6, 7], $values);
    }

    public function testDayOfWeekFromInstantOnMonday(): void
    {
        /** @Given an Instant on Monday 2026-02-16 */
        $instant = Instant::fromString(value: '2026-02-16T10:00:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Monday */
        self::assertSame(DayOfWeek::Monday, $day);
    }

    public function testDayOfWeekFromInstantOnTuesday(): void
    {
        /** @Given an Instant on Tuesday 2026-02-17 */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Tuesday */
        self::assertSame(DayOfWeek::Tuesday, $day);
    }

    public function testDayOfWeekFromInstantOnWednesday(): void
    {
        /** @Given an Instant on Wednesday 2026-02-18 */
        $instant = Instant::fromString(value: '2026-02-18T14:30:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Wednesday */
        self::assertSame(DayOfWeek::Wednesday, $day);
    }

    public function testDayOfWeekFromInstantOnThursday(): void
    {
        /** @Given an Instant at midnight on Thursday 2026-02-19 */
        $instant = Instant::fromString(value: '2026-02-19T00:00:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Thursday */
        self::assertSame(DayOfWeek::Thursday, $day);
    }

    public function testDayOfWeekFromInstantOnFriday(): void
    {
        /** @Given an Instant on Friday 2026-02-20 */
        $instant = Instant::fromString(value: '2026-02-20T17:00:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Friday */
        self::assertSame(DayOfWeek::Friday, $day);
    }

    public function testDayOfWeekFromInstantOnSaturday(): void
    {
        /** @Given an Instant on Saturday 2026-02-21 */
        $instant = Instant::fromString(value: '2026-02-21T08:00:00+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Saturday */
        self::assertSame(DayOfWeek::Saturday, $day);
    }

    public function testDayOfWeekFromInstantOnSunday(): void
    {
        /** @Given an Instant on Sunday 2026-02-22 */
        $instant = Instant::fromString(value: '2026-02-22T23:59:59+00:00');

        /** @When deriving the day of the week from the Instant */
        $day = DayOfWeek::fromInstant(instant: $instant);

        /** @Then the day should be Sunday */
        self::assertSame(DayOfWeek::Sunday, $day);
    }

    public function testDayOfWeekWeekdayAndWeekendAreMutuallyExclusive(): void
    {
        /** @Given all days of the week */
        $days = DayOfWeek::cases();

        /** @When checking that weekday and weekend are mutually exclusive for each day */
        $conflicts = array_filter(
            $days,
            static fn(DayOfWeek $day): bool => $day->isWeekday() === $day->isWeekend()
        );

        /** @Then no day should be both a weekday and a weekend day */
        self::assertCount(0, $conflicts);
    }

    public function testDayOfWeekExactlyFiveWeekdays(): void
    {
        /** @Given all days of the week */
        $allDays = DayOfWeek::cases();

        /** @When filtering for weekdays */
        $weekdays = array_filter(
            $allDays,
            static fn(DayOfWeek $day): bool => $day->isWeekday()
        );

        /** @Then there should be exactly 5 weekdays */
        self::assertCount(5, $weekdays);
    }

    public function testDayOfWeekExactlyTwoWeekendDays(): void
    {
        /** @Given all days of the week */
        $allDays = DayOfWeek::cases();

        /** @When filtering for weekend days */
        $weekends = array_filter(
            $allDays,
            static fn(DayOfWeek $day): bool => $day->isWeekend()
        );

        /** @Then there should be exactly 2 weekend days */
        self::assertCount(2, $weekends);
    }

    #[DataProvider('sameDayDistanceDataProvider')]
    public function testDayOfWeekDistanceToSameDayReturnsZero(DayOfWeek $day): void
    {
        /** @Given the same day of the week */

        /** @When computing the forward distance to itself */
        $distance = $day->distanceTo(other: $day);

        /** @Then the distance to itself should be zero */
        self::assertSame(0, $distance);
    }

    #[DataProvider('forwardDistanceDataProvider')]
    public function testDayOfWeekDistanceToForward(DayOfWeek $from, DayOfWeek $to, int $expectedDistance): void
    {
        /** @Given a starting day and a target day */

        /** @When computing the forward distance */
        $distance = $from->distanceTo(other: $to);

        /** @Then the forward distance should match the expected value */
        self::assertSame($expectedDistance, $distance);
    }

    #[DataProvider('wrapAroundDistanceDataProvider')]
    public function testDayOfWeekDistanceToWrapsAroundWeek(DayOfWeek $from, DayOfWeek $to, int $expectedDistance): void
    {
        /** @Given a starting day that is after the target day in the week */

        /** @When computing the forward distance */
        $distance = $from->distanceTo(other: $to);

        /** @Then the distance should wrap forward through the end of the week */
        self::assertSame($expectedDistance, $distance);
    }

    #[DataProvider('asymmetricDistanceDataProvider')]
    public function testDayOfWeekDistanceToIsNotSymmetric(
        DayOfWeek $from,
        DayOfWeek $to,
        int $expectedForward,
        int $expectedBackward
    ): void {
        /** @Given two distinct days of the week */

        /** @When computing the forward distance */
        $forward = $from->distanceTo(other: $to);

        /** @And computing the backward distance */
        $backward = $to->distanceTo(other: $from);

        /** @Then the forward and backward distances should differ */
        self::assertSame($expectedForward, $forward);
        self::assertSame($expectedBackward, $backward);

        /** @And together they should complete a full week */
        self::assertSame(7, $expectedForward + $expectedBackward);
    }

    #[DataProvider('allPairsDistanceDataProvider')]
    public function testDayOfWeekDistanceToNeverExceedsSix(DayOfWeek $from, DayOfWeek $to): void
    {
        /** @Given any pair of days */

        /** @When computing the forward distance */
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
