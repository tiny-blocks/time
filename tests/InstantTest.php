<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Internal\Exceptions\InvalidInstant;

final class InstantTest extends TestCase
{
    public function testInstantNowIsInUtc(): void
    {
        /** @Given the current moment */
        /** @When creating an Instant from now */
        $instant = Instant::now();

        /** @Then the DateTimeImmutable timezone should be UTC */
        self::assertSame('UTC', $instant->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testInstantNowIsCloseToCurrentTime(): void
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

    public function testInstantNowPreservesMicrosecondPrecision(): void
    {
        /** @Given an Instant created from now */
        $instant = Instant::now();

        /** @When formatting the underlying DateTimeImmutable with microseconds */
        $microseconds = (int)$instant->toDateTimeImmutable()->format('u');

        /** @Then the microsecond component should be representable (six digits available) */
        self::assertGreaterThanOrEqual(0, $microseconds);
        self::assertLessThanOrEqual(999999, $microseconds);
    }

    public function testInstantNowIso8601HasNoFractionalSeconds(): void
    {
        /** @Given an Instant created from now */
        $instant = Instant::now();

        /** @When formatting as ISO 8601 */
        $iso = $instant->toIso8601();

        /** @Then the output should match YYYY-MM-DDTHH:MM:SS+00:00 without fractions */
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $iso);
    }

    public function testInstantNowProducesDistinctInstances(): void
    {
        /** @Given two Instants created from now in sequence */
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

    #[DataProvider('validStringsDataProvider')]
    public function testInstantFromString(
        string $value,
        string $expectedIso8601,
        int $expectedUnixSeconds
    ): void {
        /** @Given a valid date-time string with offset */
        /** @When creating an Instant from the string */
        $instant = Instant::fromString(value: $value);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());

        /** @And the Unix seconds should match the expected timestamp */
        self::assertSame($expectedUnixSeconds, $instant->toUnixSeconds());
    }

    #[DataProvider('unixSecondsDataProvider')]
    public function testInstantFromUnixSeconds(
        int $seconds,
        string $expectedIso8601
    ): void {
        /** @Given a valid Unix timestamp in seconds */
        /** @When creating an Instant from Unix seconds */
        $instant = Instant::fromUnixSeconds(seconds: $seconds);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());

        /** @And the Unix seconds should round-trip correctly */
        self::assertSame($seconds, $instant->toUnixSeconds());
    }

    public function testInstantToDateTimeImmutableReturnsUtc(): void
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

    public function testInstantFromStringNormalizesToUtc(): void
    {
        /** @Given a date-time string with a positive offset */
        $instant = Instant::fromString(value: '2026-02-17T18:00:00+03:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T15:00:00+00:00', $instant->toIso8601());

        /** @And the DateTimeImmutable timezone should be UTC */
        self::assertSame('UTC', $instant->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testInstantFromStringWithNegativeOffset(): void
    {
        /** @Given a date-time string with a negative offset */
        $instant = Instant::fromString(value: '2026-02-17T07:00:00-05:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T12:00:00+00:00', $instant->toIso8601());
    }

    public function testInstantFromStringWithUtcOffset(): void
    {
        /** @Given a date-time string already in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the ISO 8601 output should remain unchanged */
        self::assertSame('2026-02-17T10:30:00+00:00', $instant->toIso8601());
    }

    public function testInstantFromUnixSecondsEpoch(): void
    {
        /** @Given Unix timestamp zero (epoch) */
        $instant = Instant::fromUnixSeconds(seconds: 0);

        /** @Then the ISO 8601 output should be the Unix epoch in UTC */
        self::assertSame('1970-01-01T00:00:00+00:00', $instant->toIso8601());

        /** @And the Unix seconds should be zero */
        self::assertSame(0, $instant->toUnixSeconds());
    }

    public function testInstantFromUnixSecondsNegativeValue(): void
    {
        /** @Given a negative Unix timestamp representing a date before the epoch */
        $instant = Instant::fromUnixSeconds(seconds: -86400);

        /** @Then the ISO 8601 output should be one day before the epoch */
        self::assertSame('1969-12-31T00:00:00+00:00', $instant->toIso8601());

        /** @And the Unix seconds should round-trip correctly */
        self::assertSame(-86400, $instant->toUnixSeconds());
    }

    public function testInstantFromUnixSecondsToDateTimeImmutableIsUtc(): void
    {
        /** @Given an Instant created from Unix seconds */
        $instant = Instant::fromUnixSeconds(seconds: 1771324200);

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    public function testInstantFromStringAndFromUnixSecondsProduceSameResult(): void
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

    public function testInstantDateTimeImmutablePreservesMicroseconds(): void
    {
        /** @Given an Instant created from a valid string */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the format should support microsecond precision */
        $formatted = $dateTime->format('Y-m-d\TH:i:s.u');
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}$/', $formatted);
    }

    public function testInstantIso8601OutputNeverContainsFractionalSeconds(): void
    {
        /** @Given an Instant created from any valid input */
        $instant = Instant::fromString(value: '2026-06-15T23:59:59+00:00');

        /** @When formatting as ISO 8601 */
        $iso = $instant->toIso8601();

        /** @Then the output should match YYYY-MM-DDTHH:MM:SS+00:00 without fractions */
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $iso);
    }

    public function testInstantFromStringWithDayBoundaryOffset(): void
    {
        /** @Given a date-time string where the UTC conversion crosses a day boundary */
        $instant = Instant::fromString(value: '2026-02-18T01:00:00+03:00');

        /** @Then the ISO 8601 output should reflect the previous day in UTC */
        self::assertSame('2026-02-17T22:00:00+00:00', $instant->toIso8601());
    }

    public function testInstantFromStringWithMaxPositiveOffset(): void
    {
        /** @Given a date-time string with the maximum positive UTC offset (+14:00) */
        $instant = Instant::fromString(value: '2026-02-17T14:00:00+14:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T00:00:00+00:00', $instant->toIso8601());
    }

    public function testInstantFromStringWithMaxNegativeOffset(): void
    {
        /** @Given a date-time string with the maximum negative UTC offset (-12:00) */
        $instant = Instant::fromString(value: '2026-02-16T12:00:00-12:00');

        /** @Then the ISO 8601 output should be normalized to UTC */
        self::assertSame('2026-02-17T00:00:00+00:00', $instant->toIso8601());
    }

    #[DataProvider('invalidStringsDataProvider')]
    public function testInstantWhenInvalidString(string $value): void
    {
        /** @Given an invalid date-time string */
        /** @Then an InvalidInstant exception should be thrown */
        $this->expectException(InvalidInstant::class);
        $this->expectExceptionMessage(sprintf('The value <%s> could not be decoded into a valid instant.', $value));

        /** @When trying to create an Instant from the invalid string */
        Instant::fromString(value: $value);
    }

    #[DataProvider('validDatabaseStringsDataProvider')]
    public function testInstantFromDatabaseString(
        string $value,
        string $expectedIso8601
    ): void {
        /** @Given a valid database date-time string in UTC */
        /** @When creating an Instant from the string */
        $instant = Instant::fromString(value: $value);

        /** @Then the ISO 8601 representation should match the expected UTC value */
        self::assertSame($expectedIso8601, $instant->toIso8601());
    }

    public function testInstantFromDatabaseStringPreservesMicroseconds(): void
    {
        /** @Given a database date-time string with microsecond precision */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the microseconds should be preserved */
        self::assertSame('106011', $dateTime->format('u'));
    }

    public function testInstantFromDatabaseStringWithoutMicrosecondsHasZeroMicroseconds(): void
    {
        /** @Given a database date-time string without microseconds */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21');

        /** @When accessing the underlying DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the microseconds should be zero */
        self::assertSame('000000', $dateTime->format('u'));
    }

    public function testInstantFromDatabaseStringIsInUtc(): void
    {
        /** @Given a database date-time string */
        $instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

        /** @When converting to DateTimeImmutable */
        $dateTime = $instant->toDateTimeImmutable();

        /** @Then the timezone should be UTC */
        self::assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    public function testPlusShiftsForwardByDuration(): void
    {
        /** @Given an Instant at a known time and a Duration of 30 minutes */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $duration = Duration::ofMinutes(minutes: 30);

        /** @When adding the Duration */
        $result = $instant->plus(duration: $duration);

        /** @Then the result should be 30 minutes later */
        self::assertSame('2026-02-17T10:30:00+00:00', $result->toIso8601());
    }

    public function testPlusWithZeroDurationReturnsSameTime(): void
    {
        /** @Given an Instant and a zero Duration */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding zero Duration */
        $result = $instant->plus(duration: Duration::zero());

        /** @Then the result should be the same time */
        self::assertSame('2026-02-17T10:00:00+00:00', $result->toIso8601());
    }

    public function testPlusCrossesDayBoundary(): void
    {
        /** @Given an Instant near the end of the day */
        $instant = Instant::fromString(value: '2026-02-17T23:30:00+00:00');

        /** @When adding 1 hour */
        $result = $instant->plus(duration: Duration::ofHours(hours: 1));

        /** @Then the result should cross into the next day */
        self::assertSame('2026-02-18T00:30:00+00:00', $result->toIso8601());
    }

    public function testPlusPreservesUtcTimezone(): void
    {
        /** @Given an Instant in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding a Duration */
        $result = $instant->plus(duration: Duration::ofMinutes(minutes: 90));

        /** @Then the result should remain in UTC */
        self::assertSame('UTC', $result->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testPlusWithLargeDuration(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding 1 day */
        $result = $instant->plus(duration: Duration::ofDays(days: 1));

        /** @Then the result should be exactly one day later */
        self::assertSame('2026-02-18T10:00:00+00:00', $result->toIso8601());
    }

    public function testMinusShiftsBackwardByDuration(): void
    {
        /** @Given an Instant at a known time and a Duration of 30 minutes */
        $instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $duration = Duration::ofMinutes(minutes: 30);

        /** @When subtracting the Duration */
        $result = $instant->minus(duration: $duration);

        /** @Then the result should be 30 minutes earlier */
        self::assertSame('2026-02-17T10:00:00+00:00', $result->toIso8601());
    }

    public function testMinusWithZeroDurationReturnsSameTime(): void
    {
        /** @Given an Instant and a zero Duration */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting zero Duration */
        $result = $instant->minus(duration: Duration::zero());

        /** @Then the result should be the same time */
        self::assertSame('2026-02-17T10:00:00+00:00', $result->toIso8601());
    }

    public function testMinusCrossesDayBoundaryBackward(): void
    {
        /** @Given an Instant at the start of the day */
        $instant = Instant::fromString(value: '2026-02-17T00:30:00+00:00');

        /** @When subtracting 1 hour */
        $result = $instant->minus(duration: Duration::ofHours(hours: 1));

        /** @Then the result should cross into the previous day */
        self::assertSame('2026-02-16T23:30:00+00:00', $result->toIso8601());
    }

    public function testMinusPreservesUtcTimezone(): void
    {
        /** @Given an Instant in UTC */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting a Duration */
        $result = $instant->minus(duration: Duration::ofMinutes(minutes: 90));

        /** @Then the result should remain in UTC */
        self::assertSame('UTC', $result->toDateTimeImmutable()->getTimezone()->getName());
    }

    public function testPlusAndMinusAreInverse(): void
    {
        /** @Given an Instant and a Duration */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $duration = Duration::ofMinutes(minutes: 45);

        /** @When adding and then subtracting the same Duration */
        $result = $instant->plus(duration: $duration)->minus(duration: $duration);

        /** @Then the result should be the original time */
        self::assertSame($instant->toIso8601(), $result->toIso8601());
        self::assertSame($instant->toUnixSeconds(), $result->toUnixSeconds());
    }

    public function testPlusResultIsAfterOriginal(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When adding a positive Duration */
        $later = $instant->plus(duration: Duration::ofMinutes(minutes: 30));

        /** @Then the result should be after the original */
        self::assertTrue($later->isAfter(other: $instant));
        self::assertTrue($instant->isBefore(other: $later));
    }

    public function testMinusResultIsBeforeOriginal(): void
    {
        /** @Given an Instant at a known time */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @When subtracting a positive Duration */
        $earlier = $instant->minus(duration: Duration::ofMinutes(minutes: 30));

        /** @Then the result should be before the original */
        self::assertTrue($earlier->isBefore(other: $instant));
        self::assertTrue($instant->isAfter(other: $earlier));
    }

    public function testDurationUntilReturnsAbsoluteDistance(): void
    {
        /** @Given two instants 30 minutes apart */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the duration should be 1800 seconds regardless of direction */
        self::assertSame(1800, $earlier->durationUntil(other: $later)->seconds);
        self::assertSame(1800, $later->durationUntil(other: $earlier)->seconds);
    }

    public function testDurationUntilSameInstantIsZero(): void
    {
        /** @Given two instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then the duration between them should be zero */
        $duration = $instant->durationUntil(other: $same);
        self::assertSame(0, $duration->seconds);
        self::assertTrue($duration->isZero());
    }

    public function testDurationUntilIsSymmetric(): void
    {
        /** @Given two distinct instants */
        $a = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $b = Instant::fromString(value: '2026-02-17T11:00:00+00:00');

        /** @Then a->durationUntil(b) should equal b->durationUntil(a) */
        self::assertSame(
            $a->durationUntil(other: $b)->seconds,
            $b->durationUntil(other: $a)->seconds
        );
    }

    public function testDurationUntilAcrossDayBoundary(): void
    {
        /** @Given two instants crossing midnight */
        $before = Instant::fromString(value: '2026-02-17T23:00:00+00:00');
        $after = Instant::fromString(value: '2026-02-18T01:00:00+00:00');

        /** @Then the duration should be 7200 seconds (2 hours) */
        self::assertSame(7200, $before->durationUntil(other: $after)->seconds);
    }

    public function testDurationUntilConsistentWithPlusAndMinus(): void
    {
        /** @Given an Instant and a Duration of 90 minutes */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $duration = Duration::ofMinutes(minutes: 90);
        $shifted = $instant->plus(duration: $duration);

        /** @Then the durationUntil should equal the original Duration */
        self::assertSame($duration->seconds, $instant->durationUntil(other: $shifted)->seconds);
    }

    public function testDurationUntilWithDifferentOrigins(): void
    {
        /** @Given an Instant from a string with offset and from Unix seconds */
        $fromString = Instant::fromString(value: '2026-02-17T13:30:00-03:00');
        $fromUnix = Instant::fromUnixSeconds(seconds: $fromString->toUnixSeconds());

        /** @Then the duration between them should be zero */
        self::assertTrue($fromString->durationUntil(other: $fromUnix)->isZero());
    }

    public function testIsBeforeReturnsTrueWhenEarlier(): void
    {
        /** @Given two Instants where the first is earlier */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the earlier instant should be before the later */
        self::assertTrue($earlier->isBefore(other: $later));
    }

    public function testIsBeforeReturnsFalseWhenLater(): void
    {
        /** @Given two Instants where the first is later */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then the later instant should not be before the earlier */
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testIsBeforeReturnsFalseWhenEqual(): void
    {
        /** @Given two Instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then isBefore should return false for equal instants */
        self::assertFalse($instant->isBefore(other: $same));
    }

    public function testIsAfterReturnsTrueWhenLater(): void
    {
        /** @Given two Instants where the first is later */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then the later instant should be after the earlier */
        self::assertTrue($later->isAfter(other: $earlier));
    }

    public function testIsAfterReturnsFalseWhenEarlier(): void
    {
        /** @Given two Instants where the first is earlier */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the earlier instant should not be after the later */
        self::assertFalse($earlier->isAfter(other: $later));
    }

    public function testIsAfterReturnsFalseWhenEqual(): void
    {
        /** @Given two Instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then isAfter should return false for equal instants */
        self::assertFalse($instant->isAfter(other: $same));
    }

    public function testIsBeforeOrEqualReturnsTrueWhenEarlier(): void
    {
        /** @Given two Instants where the first is earlier */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the earlier instant should be before or equal to the later */
        self::assertTrue($earlier->isBeforeOrEqual(other: $later));
    }

    public function testIsBeforeOrEqualReturnsTrueWhenEqual(): void
    {
        /** @Given two Instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then isBeforeOrEqual should return true for equal instants */
        self::assertTrue($instant->isBeforeOrEqual(other: $same));
    }

    public function testIsBeforeOrEqualReturnsFalseWhenLater(): void
    {
        /** @Given two Instants where the first is later */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then the later instant should not be before or equal to the earlier */
        self::assertFalse($later->isBeforeOrEqual(other: $earlier));
    }

    public function testIsAfterOrEqualReturnsTrueWhenLater(): void
    {
        /** @Given two Instants where the first is later */
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then the later instant should be after or equal to the earlier */
        self::assertTrue($later->isAfterOrEqual(other: $earlier));
    }

    public function testIsAfterOrEqualReturnsTrueWhenEqual(): void
    {
        /** @Given two Instants at the same moment */
        $instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $same = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

        /** @Then isAfterOrEqual should return true for equal instants */
        self::assertTrue($instant->isAfterOrEqual(other: $same));
    }

    public function testIsAfterOrEqualReturnsFalseWhenEarlier(): void
    {
        /** @Given two Instants where the first is earlier */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then the earlier instant should not be after or equal to the later */
        self::assertFalse($earlier->isAfterOrEqual(other: $later));
    }

    public function testIsBeforeAndIsAfterAreMutuallyExclusive(): void
    {
        /** @Given two distinct Instants */
        $earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
        $later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

        /** @Then isBefore and isAfter should be mutually exclusive */
        self::assertTrue($earlier->isBefore(other: $later));
        self::assertFalse($earlier->isAfter(other: $later));
        self::assertTrue($later->isAfter(other: $earlier));
        self::assertFalse($later->isBefore(other: $earlier));
    }

    public function testComparisonWithDifferentOriginsProducesSameResult(): void
    {
        /** @Given an Instant from a string with offset */
        $fromString = Instant::fromString(value: '2026-02-17T13:30:00-03:00');

        /** @And an Instant from the equivalent Unix seconds */
        $fromUnix = Instant::fromUnixSeconds(seconds: $fromString->toUnixSeconds());

        /** @Then both should be equal by all comparison methods */
        self::assertFalse($fromString->isBefore(other: $fromUnix));
        self::assertFalse($fromString->isAfter(other: $fromUnix));
        self::assertTrue($fromString->isBeforeOrEqual(other: $fromUnix));
        self::assertTrue($fromString->isAfterOrEqual(other: $fromUnix));
    }

    public static function validStringsDataProvider(): array
    {
        return [
            'UTC offset'             => [
                'value'               => '2026-02-17T10:30:00+00:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Midnight UTC'           => [
                'value'               => '2026-01-01T00:00:00+00:00',
                'expectedIso8601'     => '2026-01-01T00:00:00+00:00',
                'expectedUnixSeconds' => 1767225600
            ],
            'End of day UTC'         => [
                'value'               => '2026-02-17T23:59:59+00:00',
                'expectedIso8601'     => '2026-02-17T23:59:59+00:00',
                'expectedUnixSeconds' => 1771372799
            ],
            'Positive offset +05:30' => [
                'value'               => '2026-02-17T16:00:00+05:30',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -03:00' => [
                'value'               => '2026-02-17T07:30:00-03:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -05:00' => [
                'value'               => '2026-02-17T05:30:00-05:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Positive offset +09:00' => [
                'value'               => '2026-02-17T19:30:00+09:00',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
            ],
            'Negative offset -09:30' => [
                'value'               => '2026-02-17T01:00:00-09:30',
                'expectedIso8601'     => '2026-02-17T10:30:00+00:00',
                'expectedUnixSeconds' => 1771324200
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

    public static function invalidStringsDataProvider(): array
    {
        return [
            'Date only'                          => ['value' => '2026-02-17'],
            'Time only'                          => ['value' => '10:30:00'],
            'Plain text'                         => ['value' => 'not-a-date'],
            'Invalid day'                        => ['value' => '2026-02-30T10:30:00+00:00'],
            'Empty string'                       => ['value' => ''],
            'Invalid month'                      => ['value' => '2026-13-17T10:30:00+00:00'],
            'Missing offset'                     => ['value' => '2026-02-17T10:30:00'],
            'Truncated offset'                   => ['value' => '2026-02-17T10:30:00+00'],
            'Slash-separated date'               => ['value' => '2026/02/17T10:30:00+00:00'],
            'Missing time separator'             => ['value' => '2026-02-17 10:30:00+00:00'],
            'Z suffix instead offset'            => ['value' => '2026-02-17T10:30:00Z'],
            'With fractional seconds'            => ['value' => '2026-02-17T10:30:00.123456+00:00'],
            'Unix timestamp as string'           => ['value' => '1771324200'],
            'Database format with invalid day'   => ['value' => '2026-02-30 08:27:21.106011'],
            'Database format with T separator'   => ['value' => '2026-02-17T08:27:21.106011'],
            'Database format with invalid month' => ['value' => '2026-13-17 08:27:21.106011']
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
