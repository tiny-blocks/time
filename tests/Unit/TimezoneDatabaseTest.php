<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use TinyBlocks\Time\Internal\TimezoneDatabase;

final class TimezoneDatabaseTest extends TestCase
{
    protected function tearDown(): void
    {
        new ReflectionProperty(class: TimezoneDatabase::class, property: 'identifiers')->setValue(null, null);
    }

    public function testConstructorWhenInvokedViaReflectionThenInstanceIsCreated(): void
    {
        /** @Given an instance allocated without invoking the private constructor */
        $instance = new ReflectionClass(TimezoneDatabase::class)->newInstanceWithoutConstructor();

        /** @When the private constructor of the static-only collaborator is invoked through reflection */
        new ReflectionMethod(TimezoneDatabase::class, '__construct')->invoke($instance);

        /** @Then the collaborator is instantiated */
        self::assertInstanceOf(TimezoneDatabase::class, $instance);
    }

    public function testOmitsWhenIdentifiersAreMemoizedThenTheMemoizedLookupIsReused(): void
    {
        /** @Given a reader for the memoized identifiers of the timezone database */
        $identifiers = new ReflectionProperty(class: TimezoneDatabase::class, property: 'identifiers');

        /** @And a memoized lookup holding only an identifier the timezone database does not list */
        $identifiers->setValue(null, ['Memoized/Zone' => true]);

        /** @When looking up that identifier */
        $omits = TimezoneDatabase::omits(identifier: 'Memoized/Zone');

        /** @Then the memoized lookup answers instead of a freshly built one */
        self::assertFalse($omits);
    }
}
