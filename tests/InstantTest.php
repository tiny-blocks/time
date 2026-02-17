<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
            'Date only'                => ['value' => '2026-02-17'],
            'Time only'                => ['value' => '10:30:00'],
            'Plain text'               => ['value' => 'not-a-date'],
            'Invalid day'              => ['value' => '2026-02-30T10:30:00+00:00'],
            'Empty string'             => ['value' => ''],
            'Invalid month'            => ['value' => '2026-13-17T10:30:00+00:00'],
            'Missing offset'           => ['value' => '2026-02-17T10:30:00'],
            'Truncated offset'         => ['value' => '2026-02-17T10:30:00+00'],
            'Slash-separated date'     => ['value' => '2026/02/17T10:30:00+00:00'],
            'Missing time separator'   => ['value' => '2026-02-17 10:30:00+00:00'],
            'Z suffix instead offset'  => ['value' => '2026-02-17T10:30:00Z'],
            'With fractional seconds'  => ['value' => '2026-02-17T10:30:00.123456+00:00'],
            'Unix timestamp as string' => ['value' => '1771324200']
        ];
    }
}
