<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Internal\Exceptions\InvalidTimezone;
use TinyBlocks\Time\Timezone;
use TinyBlocks\Time\Timezones;

final class TimezonesTest extends TestCase
{
    public function testTimezonesFromSingleTimezone(): void
    {
        /** @Given a single Timezone object */
        $timezone = Timezone::from(identifier: 'America/Sao_Paulo');

        /** @When creating a Timezones collection */
        $timezones = Timezones::from($timezone);

        /** @Then the collection should contain exactly one item */
        self::assertSame(1, $timezones->count());

        /** @And the item should match the original Timezone */
        self::assertSame('America/Sao_Paulo', $timezones->all()[0]->value);
    }

    public function testTimezonesFromMultipleTimezones(): void
    {
        /** @Given multiple Timezone objects */
        $first = Timezone::from(identifier: 'America/Sao_Paulo');
        $second = Timezone::from(identifier: 'America/New_York');
        $third = Timezone::from(identifier: 'Asia/Tokyo');

        /** @When creating a Timezones collection */
        $timezones = Timezones::from($first, $second, $third);

        /** @Then the collection should contain all three items */
        self::assertSame(3, $timezones->count());

        /** @And they should be in the same order */
        self::assertSame('America/Sao_Paulo', $timezones->all()[0]->value);
        self::assertSame('America/New_York', $timezones->all()[1]->value);
        self::assertSame('Asia/Tokyo', $timezones->all()[2]->value);
    }

    public function testTimezonesFromStrings(): void
    {
        /** @Given valid IANA identifier strings */
        /** @When creating a Timezones collection from strings */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Europe/London');

        /** @Then the collection should contain all three items */
        self::assertSame(3, $timezones->count());

        /** @And the values should match the input order */
        self::assertSame(['UTC', 'America/Sao_Paulo', 'Europe/London'], $timezones->toStrings());
    }

    public function testTimezonesFromStringsWithInvalidIdentifier(): void
    {
        /** @Given a mix of valid and invalid identifier strings */
        /** @Then an InvalidTimezone exception should be thrown */
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('Timezone <Invalid/Zone> is invalid.');

        /** @When creating a Timezones collection with the invalid identifier */
        Timezones::fromStrings('UTC', 'Invalid/Zone');
    }

    public function testTimezonesContainsReturnsTrueForExistingIdentifier(): void
    {
        /** @Given a Timezones collection with known identifiers */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'America/New_York');

        /** @Then contains should return true for an existing identifier */
        self::assertTrue($timezones->contains(iana: 'America/Sao_Paulo'));
    }

    public function testTimezonesContainsReturnsFalseForMissingIdentifier(): void
    {
        /** @Given a Timezones collection with known identifiers */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'America/New_York');

        /** @Then contains should return false for a non-existing identifier */
        self::assertFalse($timezones->contains(iana: 'Asia/Tokyo'));
    }

    public function testTimezonesFindByIdentifierReturnsMatchingTimezone(): void
    {
        /** @Given a Timezones collection with multiple identifiers */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo');

        /** @When searching for an existing identifier */
        $found = $timezones->findByIdentifier(iana: 'Asia/Tokyo');

        /** @Then the matching Timezone should be returned */
        self::assertNotNull($found);
        self::assertSame('Asia/Tokyo', $found->value);
    }

    public function testTimezonesFindByIdentifierReturnsNullWhenNotFound(): void
    {
        /** @Given a Timezones collection without Europe/London */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @When searching for a non-existing identifier */
        $found = $timezones->findByIdentifier(iana: 'Europe/London');

        /** @Then null should be returned */
        self::assertNull($found);
    }

    public function testTimezonesCountMatchesAllSize(): void
    {
        /** @Given a Timezones collection with four items */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo', 'Europe/London');

        /** @Then count() should match the number of items in all() */
        self::assertCount($timezones->count(), $timezones->all());
    }

    public function testTimezonesIsCountable(): void
    {
        /** @Given a Timezones collection */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @Then the native count() function should work */
        self::assertSame(2, count($timezones));
    }

    public function testTimezonesToStringsReturnsPlainIdentifiers(): void
    {
        /** @Given a Timezones collection */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'Asia/Tokyo');

        /** @When converting to strings */
        $strings = $timezones->toStrings();

        /** @Then each element should match its corresponding Timezone value */
        $all = $timezones->all();

        foreach ($strings as $index => $string) {
            self::assertIsString($string);
            self::assertSame($all[$index]->value, $string);
        }
    }

    public function testTimezonesFromEmptyReturnsEmptyCollection(): void
    {
        /** @Given no Timezone objects */
        /** @When creating an empty Timezones collection */
        $timezones = Timezones::from();

        /** @Then the collection should be empty */
        self::assertSame(0, $timezones->count());
        self::assertSame([], $timezones->all());
        self::assertSame([], $timezones->toStrings());
    }

    public function testTimezonesPreservesInsertionOrder(): void
    {
        /** @Given identifiers in a specific order */
        $identifiers = ['Pacific/Auckland', 'Asia/Tokyo', 'UTC', 'America/New_York'];

        /** @When creating a collection from those strings */
        $timezones = Timezones::fromStrings(...$identifiers);

        /** @Then toStrings should preserve the original order */
        self::assertSame($identifiers, $timezones->toStrings());
    }

    public function testTimezonesCreatedFromSameIdentifiersAreConsistent(): void
    {
        /** @Given two Timezones collections created from the same identifiers */
        $first = Timezones::fromStrings('UTC', 'America/Sao_Paulo');
        $second = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @Then their string representations should be identical */
        self::assertSame($first->toStrings(), $second->toStrings());

        /** @And their counts should match */
        self::assertSame($first->count(), $second->count());
    }
}
