<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Internal\Exceptions\InvalidTimezone;
use TinyBlocks\Time\Timezone;

final class TimezoneTest extends TestCase
{
    public function testTimezoneUtcHasCorrectValue(): void
    {
        /** @Given a request for the UTC timezone */
        /** @When creating a UTC Timezone */
        $timezone = Timezone::utc();

        /** @Then the value should be UTC */
        self::assertSame('UTC', $timezone->value);
    }

    public function testTimezoneUtcToStringReturnsUtc(): void
    {
        /** @Given a UTC Timezone */
        $timezone = Timezone::utc();

        /** @When converting to string */
        $result = $timezone->toString();

        /** @Then the result should be UTC */
        self::assertSame('UTC', $result);
    }

    public function testTimezoneUtcToDateTimeZoneReturnsUtc(): void
    {
        /** @Given a UTC Timezone */
        $timezone = Timezone::utc();

        /** @When converting to DateTimeZone */
        $dateTimeZone = $timezone->toDateTimeZone();

        /** @Then the DateTimeZone name should be UTC */
        self::assertSame('UTC', $dateTimeZone->getName());
    }

    #[DataProvider('validIdentifiersDataProvider')]
    public function testTimezoneFromValidIdentifier(string $identifier): void
    {
        /** @Given a valid IANA timezone identifier */
        /** @When creating a Timezone from the identifier */
        $timezone = Timezone::from(identifier: $identifier);

        /** @Then the value should match the given identifier */
        self::assertSame($identifier, $timezone->value);

        /** @And toString should return the same identifier */
        self::assertSame($identifier, $timezone->toString());
    }

    #[DataProvider('validIdentifiersDataProvider')]
    public function testTimezoneToDateTimeZoneMatchesIdentifier(string $identifier): void
    {
        /** @Given a Timezone created from a valid identifier */
        $timezone = Timezone::from(identifier: $identifier);

        /** @When converting to DateTimeZone */
        $dateTimeZone = $timezone->toDateTimeZone();

        /** @Then the DateTimeZone name should match the original identifier */
        self::assertSame($identifier, $dateTimeZone->getName());
    }

    #[DataProvider('invalidIdentifiersDataProvider')]
    public function testTimezoneWhenInvalidIdentifier(string $identifier): void
    {
        /** @Given an invalid timezone identifier */
        /** @Then an InvalidTimezone exception should be thrown */
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage(sprintf('Timezone <%s> is invalid.', $identifier));

        /** @When trying to create a Timezone from the invalid identifier */
        Timezone::from(identifier: $identifier);
    }

    public static function validIdentifiersDataProvider(): array
    {
        return [
            'UTC'               => ['identifier' => 'UTC'],
            'Asia/Tokyo'        => ['identifier' => 'Asia/Tokyo'],
            'Asia/Kolkata'      => ['identifier' => 'Asia/Kolkata'],
            'Europe/London'     => ['identifier' => 'Europe/London'],
            'Pacific/Auckland'  => ['identifier' => 'Pacific/Auckland'],
            'Australia/Sydney'  => ['identifier' => 'Australia/Sydney'],
            'America/New_York'  => ['identifier' => 'America/New_York'],
            'America/Sao_Paulo' => ['identifier' => 'America/Sao_Paulo']
        ];
    }

    public static function invalidIdentifiersDataProvider(): array
    {
        return [
            'Spaces'         => ['identifier' => 'America/ New_York'],
            'Partial'        => ['identifier' => 'America/'],
            'Plain text'     => ['identifier' => 'Invalid/Timezone'],
            'Abbreviation'   => ['identifier' => 'EST'],
            'Empty string'   => ['identifier' => ''],
            'Numeric offset' => ['identifier' => '+00:00']
        ];
    }
}
