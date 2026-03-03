<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Internal\Exceptions\InvalidPeriod;
use TinyBlocks\Time\Period;

final class PeriodTest extends TestCase
{
    public function testPeriodFromCreatesPeriodWithValidRange(): void
    {
        /** @Given an Instant at the start of the range */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And an Instant at the end of the range */
        $to = Instant::fromString(value: '2026-02-17T11:00:00+00:00');

        /** @When creating a Period */
        $period = Period::from(from: $from, to: $to);

        /** @Then the period should expose the correct boundaries */
        self::assertSame('2026-02-17T10:00:00+00:00', $period->from->toIso8601());
        self::assertSame('2026-02-17T11:00:00+00:00', $period->to->toIso8601());
    }

    public function testPeriodFromWhenStartEqualsEnd(): void
    {
        /** @Given two instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an exception indicating that start must be before end should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with equal boundaries */
        Period::from(from: $instant, to: $instant);
    }

    public function testPeriodFromWhenStartIsAfterEnd(): void
    {
        /** @Given an Instant after the intended end */
        $from = Instant::fromString(value: '2026-02-17T11:00:00+00:00');

        /** @And an Instant before the intended start */
        $to = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an exception indicating that start must be before end should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with inverted boundaries */
        Period::from(from: $from, to: $to);
    }

    public function testPeriodStartingAtCreatesPeriodFromDuration(): void
    {
        /** @Given a start Instant */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And a Duration of 30 minutes */
        $duration = Duration::fromMinutes(minutes: 30);

        /** @When creating a Period from start and Duration */
        $period = Period::startingAt(from: $from, duration: $duration);

        /** @Then the end should be 30 minutes after the start */
        self::assertSame('2026-02-17T10:00:00+00:00', $period->from->toIso8601());
        self::assertSame('2026-02-17T10:30:00+00:00', $period->to->toIso8601());
    }

    public function testPeriodStartingAtWhenDurationIsZero(): void
    {
        /** @Given a start Instant */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an exception indicating that duration must not be zero should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with zero Duration */
        Period::startingAt(from: $from, duration: Duration::zero());
    }

    public function testPeriodStartingAtCrossesDayBoundary(): void
    {
        /** @Given a start near midnight */
        $from = Instant::fromString(value: '2026-02-17T23:00:00+00:00');

        /** @And a Duration that crosses the day */
        $duration = Duration::fromHours(hours: 2);

        /** @When creating a Period */
        $period = Period::startingAt(from: $from, duration: $duration);

        /** @Then the end should be on the next day */
        self::assertSame('2026-02-18T01:00:00+00:00', $period->to->toIso8601());
    }

    public function testPeriodDurationReturnsCorrectValue(): void
    {
        /** @Given a Period spanning 1 hour */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the duration should be 3600 seconds */
        self::assertSame(3600, $duration->toSeconds());
    }

    public function testPeriodDurationFromStartingAt(): void
    {
        /** @Given a Duration of 90 minutes */
        $inputDuration = Duration::fromMinutes(minutes: 90);

        /** @And a Period created from start and that Duration */
        $period = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            duration: $inputDuration
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the duration should match the input */
        self::assertSame($inputDuration->toSeconds(), $duration->toSeconds());
    }

    public function testPeriodDurationReturnsDurationObject(): void
    {
        /** @Given a Period of 30 minutes */
        $period = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            duration: Duration::fromMinutes(minutes: 30)
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the Duration should be convertible to minutes */
        self::assertSame(30, $duration->toMinutes());
    }

    public function testPeriodContainsReturnsTrueForInstantAtStart(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking the start instant */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T10:00:00+00:00'));

        /** @Then the start should be contained (inclusive) */
        self::assertTrue($result);
    }

    public function testPeriodContainsReturnsTrueForInstantInMiddle(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant in the middle */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T10:30:00+00:00'));

        /** @Then it should be contained */
        self::assertTrue($result);
    }

    public function testPeriodContainsReturnsFalseForInstantAtEnd(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking the end instant */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T11:00:00+00:00'));

        /** @Then the end should not be contained (exclusive) */
        self::assertFalse($result);
    }

    public function testPeriodContainsReturnsFalseForInstantBeforeStart(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant before the start */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T09:59:59+00:00'));

        /** @Then it should not be contained */
        self::assertFalse($result);
    }

    public function testPeriodContainsReturnsFalseForInstantAfterEnd(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant after the end */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T11:00:01+00:00'));

        /** @Then it should not be contained */
        self::assertFalse($result);
    }

    public function testPeriodOverlapsWithReturnsTrueForPartialOverlap(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $periodA = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @And a partially overlapping Period [10:30, 11:30) */
        $periodB = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:30:00+00:00')
        );

        /** @Then both should detect overlap */
        self::assertTrue($periodA->overlapsWith(other: $periodB));
        self::assertTrue($periodB->overlapsWith(other: $periodA));
    }

    public function testPeriodOverlapsWithReturnsTrueWhenOneContainsAnother(): void
    {
        /** @Given an outer Period [09:00, 12:00) */
        $outer = Period::from(
            from: Instant::fromString(value: '2026-02-17T09:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T12:00:00+00:00')
        );

        /** @And an inner Period [10:00, 11:00) fully contained */
        $inner = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @Then both should detect overlap */
        self::assertTrue($outer->overlapsWith(other: $inner));
        self::assertTrue($inner->overlapsWith(other: $outer));
    }

    public function testPeriodOverlapsWithReturnsFalseForAdjacentPeriods(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $periodA = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @And an adjacent Period [11:00, 12:00) */
        $periodB = Period::from(
            from: Instant::fromString(value: '2026-02-17T11:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T12:00:00+00:00')
        );

        /** @Then they should not overlap (half-open intervals are disjoint when adjacent) */
        self::assertFalse($periodA->overlapsWith(other: $periodB));
        self::assertFalse($periodB->overlapsWith(other: $periodA));
    }

    public function testPeriodOverlapsWithReturnsFalseForDisjointPeriods(): void
    {
        /** @Given a Period [08:00, 09:00) */
        $periodA = Period::from(
            from: Instant::fromString(value: '2026-02-17T08:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T09:00:00+00:00')
        );

        /** @And a completely disjoint Period [14:00, 15:00) */
        $periodB = Period::from(
            from: Instant::fromString(value: '2026-02-17T14:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T15:00:00+00:00')
        );

        /** @Then they should not overlap */
        self::assertFalse($periodA->overlapsWith(other: $periodB));
        self::assertFalse($periodB->overlapsWith(other: $periodA));
    }

    public function testPeriodOverlapsWithReturnsTrueForIdenticalPeriods(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $periodA = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @And an identical Period [10:00, 11:00) */
        $periodB = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @Then they should overlap */
        self::assertTrue($periodA->overlapsWith(other: $periodB));
    }

    public function testPeriodOverlapsWithIsSymmetric(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $periodA = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @And an overlapping Period [10:30, 11:30) */
        $periodB = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:30:00+00:00')
        );

        /** @Then overlap detection should be symmetric */
        self::assertSame(
            $periodA->overlapsWith(other: $periodB),
            $periodB->overlapsWith(other: $periodA)
        );
    }

    public function testPeriodDurationIsConsistentBetweenFromAndStartingAt(): void
    {
        /** @Given a start Instant */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @And an end Instant */
        $to = Instant::fromString(value: '2026-02-17T11:30:00+00:00');

        /** @And a Period created via from() */
        $periodFromFrom = Period::from(from: $from, to: $to);

        /** @And a Period created via startingAt() with equivalent Duration */
        $periodFromStartingAt = Period::startingAt(from: $from, duration: Duration::fromMinutes(minutes: 90));

        /** @Then both should have the same Duration */
        self::assertSame(
            $periodFromFrom->duration()->toSeconds(),
            $periodFromStartingAt->duration()->toSeconds()
        );
    }

    public function testPeriodContainsIsConsistentWithOverlapsForSingleInstant(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::from(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @And a contained Instant at 10:30 */
        $contained = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @And a 1-second micro-period starting at that Instant */
        $microPeriod = Period::startingAt(from: $contained, duration: Duration::fromSeconds(seconds: 1));

        /** @Then the period should contain the instant and overlap with the micro-period */
        self::assertTrue($period->contains(instant: $contained));
        self::assertTrue($period->overlapsWith(other: $microPeriod));
    }

    public function testPeriodOverlapsWithNonOverlappingIsSymmetric(): void
    {
        /** @Given a Period [08:00, 09:00) */
        $periodA = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T08:00:00+00:00'),
            duration: Duration::fromHours(hours: 1)
        );

        /** @And a non-overlapping Period [14:00, 15:00) */
        $periodB = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T14:00:00+00:00'),
            duration: Duration::fromHours(hours: 1)
        );

        /** @Then symmetry should hold for non-overlapping case */
        self::assertSame(
            $periodA->overlapsWith(other: $periodB),
            $periodB->overlapsWith(other: $periodA)
        );
        self::assertFalse($periodA->overlapsWith(other: $periodB));
    }
}
