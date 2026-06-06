<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Time\Exceptions\InvalidTimezone;
use TinyBlocks\Time\Timezone;
use TinyBlocks\Time\Timezones;

final class TimezonesTest extends TestCase
{
    public function testFromThenEmptyCollection(): void
    {
        /** @When creating an empty Timezones collection */
        $timezones = Timezones::from();

        /** @Then the count should be zero */
        self::assertSame(0, $timezones->count());

        /** @And all() should return an empty array */
        self::assertSame([], $timezones->all());

        /** @And toStrings() should return an empty array */
        self::assertSame([], $timezones->toStrings());
    }

    public function testCountWhenTwoTimezonesThenIsCountable(): void
    {
        /** @Given a Timezones collection */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @When counting via the native count() function */
        $count = count($timezones);

        /** @Then the count should be 2 */
        self::assertSame(2, $count);
    }

    public function testCountWhenManyTimezonesThenMatchesAllSize(): void
    {
        /** @Given a Timezones collection with four items */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo', 'Europe/London');

        /** @When checking the count */
        $count = $timezones->count();

        /** @Then count should match the number of items in all() */
        self::assertCount($count, $timezones->all());
    }

    public function testContainsWhenIdentifierExistsThenReturnsTrue(): void
    {
        /** @Given a Timezones collection with known identifiers */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'America/New_York');

        /** @When checking if 'America/Sao_Paulo' is contained */
        $result = $timezones->contains(iana: 'America/Sao_Paulo');

        /** @Then it should return true */
        self::assertTrue($result);
    }

    public function testFromWhenMultipleTimezonesThenPreservesOrder(): void
    {
        /** @Given a Timezone for São Paulo */
        $first = Timezone::from(identifier: 'America/Sao_Paulo');

        /** @And a Timezone for New York */
        $second = Timezone::from(identifier: 'America/New_York');

        /** @And a Timezone for Tokyo */
        $third = Timezone::from(identifier: 'Asia/Tokyo');

        /** @When creating a Timezones collection */
        $timezones = Timezones::from($first, $second, $third);

        /** @Then the collection should contain three items */
        self::assertSame(3, $timezones->count());

        /** @And the first should be America/Sao_Paulo */
        self::assertSame('America/Sao_Paulo', $timezones->all()[0]->value);

        /** @And the second should be America/New_York */
        self::assertSame('America/New_York', $timezones->all()[1]->value);

        /** @And the third should be Asia/Tokyo */
        self::assertSame('Asia/Tokyo', $timezones->all()[2]->value);
    }

    public function testContainsWhenIdentifierMissingThenReturnsFalse(): void
    {
        /** @Given a Timezones collection with known identifiers */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'America/New_York');

        /** @When checking if 'Asia/Tokyo' is contained */
        $result = $timezones->contains(iana: 'Asia/Tokyo');

        /** @Then it should return false */
        self::assertFalse($result);
    }

    public function testFromWhenSingleTimezoneThenCollectionHasOneItem(): void
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

    public function testFromStringsWhenInvalidIdentifierThenInvalidTimezone(): void
    {
        /** @Then an InvalidTimezone exception should be thrown */
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('Timezone <Invalid/Zone> is invalid.');

        /** @When creating a Timezones collection with a mix of valid and invalid identifier strings */
        Timezones::fromStrings('UTC', 'Invalid/Zone');
    }

    public function testFindByIdentifierWhenIdentifierMissingThenReturnsNull(): void
    {
        /** @Given a Timezones collection without Europe/London */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @When searching for a non-existing identifier */
        $found = $timezones->findByIdentifier(iana: 'Europe/London');

        /** @Then null should be returned */
        self::assertNull($found);
    }

    public function testFromStringsWhenValidIdentifiersThenCollectionIsCreated(): void
    {
        /** @When creating a Timezones collection from IANA identifier strings */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Europe/London');

        /** @Then the collection should contain all three items */
        self::assertSame(3, $timezones->count());

        /** @And the values should match the input order */
        self::assertSame(['UTC', 'America/Sao_Paulo', 'Europe/London'], $timezones->toStrings());
    }

    public function testFindByIdentifierWhenIdentifierExistsThenReturnsTimezone(): void
    {
        /** @Given a Timezones collection with multiple identifiers */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo');

        /** @When searching for an existing identifier */
        $found = $timezones->findByIdentifier(iana: 'Asia/Tokyo');

        /** @Then the matching Timezone should be returned */
        self::assertNotNull($found);
        self::assertSame('Asia/Tokyo', $found->value);
    }

    public function testToStringsWhenCollectionGivenThenReturnsPlainIdentifiers(): void
    {
        /** @Given a Timezones collection */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'Asia/Tokyo');

        /** @When converting to strings */
        $strings = $timezones->toStrings();

        /** @Then each identifier should match its corresponding Timezone value */
        self::assertSame(
            array_map(static fn(Timezone $timezone): string => $timezone->value, $timezones->all()),
            $strings
        );
    }

    public function testFindByIdentifierOrUtcWhenIdentifierMissingThenReturnsUtc(): void
    {
        /** @Given a Timezones collection without Europe/London */
        $timezones = Timezones::fromStrings('America/Sao_Paulo', 'Asia/Tokyo');

        /** @When searching for a non-existing identifier */
        $found = $timezones->findByIdentifierOrUtc(iana: 'Europe/London');

        /** @Then UTC should be returned as fallback */
        self::assertSame('UTC', $found->value);
    }

    public function testFromStringsWhenSameIdentifiersThenInstancesAreConsistent(): void
    {
        /** @Given a first Timezones collection */
        $first = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @And a second Timezones collection from the same identifiers */
        $second = Timezones::fromStrings('UTC', 'America/Sao_Paulo');

        /** @When converting the first to strings */
        $strings = $first->toStrings();

        /** @Then they should be identical to the second's string representation */
        self::assertSame($strings, $second->toStrings());

        /** @And their counts should match */
        self::assertSame($first->count(), $second->count());
    }

    public function testFindByIdentifierOrUtcWhenIdentifierExistsThenReturnsTimezone(): void
    {
        /** @Given a Timezones collection with multiple identifiers */
        $timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo');

        /** @When searching for an existing identifier */
        $found = $timezones->findByIdentifierOrUtc(iana: 'Asia/Tokyo');

        /** @Then the matching Timezone should be returned */
        self::assertSame('Asia/Tokyo', $found->value);
    }

    public function testFromStringsWhenIdentifiersGivenThenInsertionOrderIsPreserved(): void
    {
        /** @Given identifiers in a specific order */
        $identifiers = ['Pacific/Auckland', 'Asia/Tokyo', 'UTC', 'America/New_York'];

        /** @When creating a collection from those strings */
        $timezones = Timezones::fromStrings(...$identifiers);

        /** @Then toStrings should preserve the original order */
        self::assertSame($identifiers, $timezones->toStrings());
    }
}
