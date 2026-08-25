<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Exceptions\InvalidTimezone;
use TinyBlocks\Time\Timezone;

final class TimezoneTest extends TestCase
{
    public function testUtcThenValueIsUtc(): void
    {
        /** @When creating a UTC Timezone */
        $timezone = Timezone::utc();

        /** @Then the value should be UTC */
        self::assertSame('UTC', $timezone->value);
    }

    public function testUtcWhenToStringThenReturnsUtc(): void
    {
        /** @Given a UTC Timezone */
        $timezone = Timezone::utc();

        /** @When converting to string */
        $result = $timezone->toString();

        /** @Then the result should be UTC */
        self::assertSame('UTC', $result);
    }

    public function testUtcWhenToDateTimeZoneThenNameIsUtc(): void
    {
        /** @Given a UTC Timezone */
        $timezone = Timezone::utc();

        /** @When converting to DateTimeZone */
        $dateTimeZone = $timezone->toDateTimeZone();

        /** @Then the DateTimeZone name should be UTC */
        self::assertSame('UTC', $dateTimeZone->getName());
    }

    #[DataProvider('validIdentifiersDataProvider')]
    public function testFromWhenValidIdentifierThenValueMatches(string $identifier): void
    {
        /** @Given a valid IANA timezone identifier */
        /** @When creating a Timezone from the identifier */
        $timezone = Timezone::from(identifier: $identifier);

        /** @Then the value should match the given identifier */
        self::assertSame($identifier, $timezone->value);

        /** @And toString should return the same identifier */
        self::assertSame($identifier, $timezone->toString());
    }

    #[DataProvider('invalidIdentifiersDataProvider')]
    public function testFromWhenInvalidIdentifierThenInvalidTimezone(string $identifier): void
    {
        /** @Given an invalid timezone identifier */
        /** @Then an InvalidTimezone exception should be thrown */
        $this->expectException(InvalidTimezone::class);
        $template = 'Timezone <%s> is invalid.';
        $this->expectExceptionMessage(sprintf($template, $identifier));

        /** @When trying to create a Timezone from the invalid identifier */
        Timezone::from(identifier: $identifier);
    }

    #[DataProvider('validIdentifiersDataProvider')]
    public function testFromWhenValidIdentifierThenDateTimeZoneNameMatches(string $identifier): void
    {
        /** @Given a Timezone created from a valid identifier */
        $timezone = Timezone::from(identifier: $identifier);

        /** @When converting to DateTimeZone */
        $dateTimeZone = $timezone->toDateTimeZone();

        /** @Then the DateTimeZone name should match the original identifier */
        self::assertSame($identifier, $dateTimeZone->getName());
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

    public function testEqualsWhenSameIdentifierThenIsTrue(): void
    {
        /** @Given two Timezones built separately from the same identifier */
        $one = Timezone::from(identifier: 'America/Sao_Paulo');
        $other = Timezone::from(identifier: 'America/Sao_Paulo');

        /** @Then they are equal by value and share the hash code */
        self::assertTrue($one->equals(other: $other));
        self::assertSame($one->hashCode(), $other->hashCode());
    }

    public function testEqualsWhenDifferentIdentifierThenIsFalse(): void
    {
        /** @Given two Timezones with different identifiers */
        $one = Timezone::utc();
        $other = Timezone::from(identifier: 'America/Sao_Paulo');

        /** @Then they are not equal and the hash codes differ */
        self::assertFalse($one->equals(other: $other));
        self::assertNotSame($one->hashCode(), $other->hashCode());
    }
}
