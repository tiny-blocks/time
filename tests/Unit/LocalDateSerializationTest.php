<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Time\Models\Holiday;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Time\LocalDate;

final class LocalDateSerializationTest extends TestCase
{
    public function testToArrayWhenLocalDateFieldThenEmitsDateOnlyString(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a holiday carrying a local date and a name */
        $holiday = new Holiday(date: LocalDate::fromString(value: '2026-05-23'), name: 'Labor Day');

        /** @When the holiday is serialized to an array */
        $array = $mapper->toArray(source: $holiday);

        /** @Then the local date is written as a date-only ISO 8601 string */
        self::assertSame(['date' => '2026-05-23', 'name' => 'Labor Day'], $array);
    }

    public function testToObjectThenToArrayWhenLocalDateFieldThenRoundTripsDateOnlyString(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a holiday rebuilt from a date-only row */
        $holiday = $mapper->toObject(type: Holiday::class, source: ['date' => '2026-05-23', 'name' => 'Labor Day']);

        /** @When the holiday is serialized back to an array */
        $array = $mapper->toArray(source: $holiday);

        /** @Then the round-trip preserves the date-only form */
        self::assertSame(['date' => '2026-05-23', 'name' => 'Labor Day'], $array);
    }
}
