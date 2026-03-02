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
    public function testOfCreatesPeriodWithValidRange(): void
    {
        /** @Given two instants where from is before to */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $to = Instant::fromString(value: '2026-02-17T11:00:00+00:00');

        /** @When creating a Period */
        $period = Period::of(from: $from, to: $to);

        /** @Then the period should expose the correct boundaries */
        self::assertSame('2026-02-17T10:00:00+00:00', $period->from->toIso8601());
        self::assertSame('2026-02-17T11:00:00+00:00', $period->to->toIso8601());
    }

    public function testOfThrowsWhenStartEqualsEnd(): void
    {
        /** @Given two instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an InvalidPeriod exception should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with equal boundaries */
        Period::of(from: $instant, to: $instant);
    }

    public function testOfThrowsWhenStartIsAfterEnd(): void
    {
        /** @Given two instants where from is after to */
        $from = Instant::fromString(value: '2026-02-17T11:00:00+00:00');
        $to = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an InvalidPeriod exception should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with inverted boundaries */
        Period::of(from: $from, to: $to);
    }

    public function testStartingAtCreatesPeriodFromDuration(): void
    {
        /** @Given a start instant and a Duration of 30 minutes */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $duration = Duration::ofMinutes(minutes: 30);

        /** @When creating a Period from start and Duration */
        $period = Period::startingAt(from: $from, duration: $duration);

        /** @Then the end should be 30 minutes after the start */
        self::assertSame('2026-02-17T10:00:00+00:00', $period->from->toIso8601());
        self::assertSame('2026-02-17T10:30:00+00:00', $period->to->toIso8601());
    }

    public function testStartingAtThrowsWhenDurationIsZero(): void
    {
        /** @Given a start instant and a zero Duration */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then an InvalidPeriod exception should be thrown */
        $this->expectException(InvalidPeriod::class);

        /** @When creating a Period with zero Duration */
        Period::startingAt(from: $from, duration: Duration::zero());
    }

    public function testStartingAtCrossesDayBoundary(): void
    {
        /** @Given a start near midnight and a Duration that crosses the day */
        $from = Instant::fromString(value: '2026-02-17T23:00:00+00:00');
        $duration = Duration::ofHours(hours: 2);

        /** @When creating a Period */
        $period = Period::startingAt(from: $from, duration: $duration);

        /** @Then the end should be on the next day */
        self::assertSame('2026-02-18T01:00:00+00:00', $period->to->toIso8601());
    }

    public function testDurationReturnsCorrectValue(): void
    {
        /** @Given a Period spanning 1 hour */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the duration should be 3600 seconds */
        self::assertSame(3600, $duration->seconds);
    }

    public function testDurationFromStartingAt(): void
    {
        /** @Given a Period created from start and Duration of 90 minutes */
        $inputDuration = Duration::ofMinutes(minutes: 90);
        $period = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            duration: $inputDuration
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the duration should match the input */
        self::assertSame($inputDuration->seconds, $duration->seconds);
    }

    public function testDurationReturnsDurationObject(): void
    {
        /** @Given a Period of 30 minutes */
        $period = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            duration: Duration::ofMinutes(minutes: 30)
        );

        /** @When getting the Duration */
        $duration = $period->duration();

        /** @Then the Duration should be convertible to minutes */
        self::assertSame(30, $duration->toMinutes());
    }

    public function testContainsReturnsTrueForInstantAtStart(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking the start instant */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T10:00:00+00:00'));

        /** @Then the start should be contained (inclusive) */
        self::assertTrue($result);
    }

    public function testContainsReturnsTrueForInstantInMiddle(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant in the middle */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T10:30:00+00:00'));

        /** @Then it should be contained */
        self::assertTrue($result);
    }

    public function testContainsReturnsFalseForInstantAtEnd(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking the end instant */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T11:00:00+00:00'));

        /** @Then the end should not be contained (exclusive) */
        self::assertFalse($result);
    }

    public function testContainsReturnsFalseForInstantBeforeStart(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant before the start */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T09:59:59+00:00'));

        /** @Then it should not be contained */
        self::assertFalse($result);
    }

    public function testContainsReturnsFalseForInstantAfterEnd(): void
    {
        /** @Given a Period [10:00, 11:00) */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @When checking an instant after the end */
        $result = $period->contains(instant: Instant::fromString(value: '2026-02-17T11:00:01+00:00'));

        /** @Then it should not be contained */
        self::assertFalse($result);
    }

    public function testOverlapsWithReturnsTrueForPartialOverlap(): void
    {
        /** @Given two partially overlapping periods */
        $periodA = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );
        $periodB = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:30:00+00:00')
        );

        /** @Then both should detect overlap */
        self::assertTrue($periodA->overlapsWith(other: $periodB));
        self::assertTrue($periodB->overlapsWith(other: $periodA));
    }

    public function testOverlapsWithReturnsTrueWhenOneContainsAnother(): void
    {
        /** @Given a period that fully contains another */
        $outer = Period::of(
            from: Instant::fromString(value: '2026-02-17T09:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T12:00:00+00:00')
        );
        $inner = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @Then both should detect overlap */
        self::assertTrue($outer->overlapsWith(other: $inner));
        self::assertTrue($inner->overlapsWith(other: $outer));
    }

    public function testOverlapsWithReturnsFalseForAdjacentPeriods(): void
    {
        /** @Given two adjacent periods [10:00, 11:00) and [11:00, 12:00) */
        $periodA = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );
        $periodB = Period::of(
            from: Instant::fromString(value: '2026-02-17T11:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T12:00:00+00:00')
        );

        /** @Then they should not overlap (half-open intervals are disjoint when adjacent) */
        self::assertFalse($periodA->overlapsWith(other: $periodB));
        self::assertFalse($periodB->overlapsWith(other: $periodA));
    }

    public function testOverlapsWithReturnsFalseForDisjointPeriods(): void
    {
        /** @Given two completely disjoint periods */
        $periodA = Period::of(
            from: Instant::fromString(value: '2026-02-17T08:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T09:00:00+00:00')
        );
        $periodB = Period::of(
            from: Instant::fromString(value: '2026-02-17T14:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T15:00:00+00:00')
        );

        /** @Then they should not overlap */
        self::assertFalse($periodA->overlapsWith(other: $periodB));
        self::assertFalse($periodB->overlapsWith(other: $periodA));
    }

    public function testOverlapsWithReturnsTrueForIdenticalPeriods(): void
    {
        /** @Given two identical periods */
        $periodA = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );
        $periodB = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );

        /** @Then they should overlap */
        self::assertTrue($periodA->overlapsWith(other: $periodB));
    }

    public function testOverlapsWithIsSymmetric(): void
    {
        /** @Given two overlapping periods */
        $periodA = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );
        $periodB = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:30:00+00:00')
        );

        /** @Then overlap detection should be symmetric */
        self::assertSame(
            $periodA->overlapsWith(other: $periodB),
            $periodB->overlapsWith(other: $periodA)
        );
    }

    public function testDurationIsConsistentBetweenOfAndStartingAt(): void
    {
        /** @Given a Period from of() and one from startingAt() with the same boundaries */
        $from = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $to = Instant::fromString(value: '2026-02-17T11:30:00+00:00');

        $periodFromOf = Period::of(from: $from, to: $to);
        $periodFromStartingAt = Period::startingAt(from: $from, duration: Duration::ofMinutes(minutes: 90));

        /** @Then both should have the same Duration */
        self::assertSame(
            $periodFromOf->duration()->seconds,
            $periodFromStartingAt->duration()->seconds
        );
    }

    public function testContainsIsConsistentWithOverlapsForSingleInstant(): void
    {
        /** @Given a Period and a 1-second micro-period at a contained instant */
        $period = Period::of(
            from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
            to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
        );
        $contained = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $microPeriod = Period::startingAt(from: $contained, duration: Duration::ofSeconds(seconds: 1));

        /** @Then the period should contain the instant and overlap with the micro-period */
        self::assertTrue($period->contains(instant: $contained));
        self::assertTrue($period->overlapsWith(other: $microPeriod));
    }

    public function testOverlapsWithNonOverlappingIsSymmetric(): void
    {
        /** @Given two non-overlapping periods */
        $periodA = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T08:00:00+00:00'),
            duration: Duration::ofHours(hours: 1)
        );
        $periodB = Period::startingAt(
            from: Instant::fromString(value: '2026-02-17T14:00:00+00:00'),
            duration: Duration::ofHours(hours: 1)
        );

        /** @Then symmetry should hold for non-overlapping case */
        self::assertSame(
            $periodA->overlapsWith(other: $periodB),
            $periodB->overlapsWith(other: $periodA)
        );
        self::assertFalse($periodA->overlapsWith(other: $periodB));
    }
}
