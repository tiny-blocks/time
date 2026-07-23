<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Time\Models\Event;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Time\Exceptions\InvalidInstant;
use TinyBlocks\Time\Instant;

final class InstantSerializationTest extends TestCase
{
    public function testToArrayWhenInstantFieldThenEmitsIso8601Seconds(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an event carrying an instant and a name */
        $event = new Event(occurredAt: Instant::fromString(value: '2026-02-17T10:30:00+00:00'), name: 'launch');

        /** @When the event is serialized to an array */
        $array = $mapper->toArray(source: $event);

        /** @Then the instant is written as a second-precision ISO 8601 string */
        self::assertSame(['occurredAt' => '2026-02-17T10:30:00+00:00', 'name' => 'launch'], $array);
    }

    public function testToObjectWhenInstantFromOffsetStringThenNormalizesToUtc(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an instant is rebuilt from a string carrying a non-UTC offset */
        $instant = $mapper->toObject(type: Instant::class, source: '2026-02-17T13:30:00-03:00');

        /** @Then the instant equals the same moment expressed in UTC */
        self::assertEquals(Instant::fromString(value: '2026-02-17T16:30:00+00:00'), $instant);
    }

    public function testToObjectWhenInstantFromInvalidStringThenThrowsInvalidInstant(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then the value cannot be decoded into a valid instant */
        $this->expectException(InvalidInstant::class);

        /** @When an instant is rebuilt from a non-date string */
        $mapper->toObject(type: Instant::class, source: 'not-a-date');
    }
}
