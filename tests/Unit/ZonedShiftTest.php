<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\Time\Internal\ZonedShift;

final class ZonedShiftTest extends TestCase
{
    public function testConstructorWhenInvokedViaReflectionThenInstanceIsCreated(): void
    {
        /** @Given an instance allocated without invoking the private constructor */
        $instance = new ReflectionClass(ZonedShift::class)->newInstanceWithoutConstructor();

        /** @When the private constructor of the static-only collaborator is invoked through reflection */
        new ReflectionMethod(ZonedShift::class, '__construct')->invoke($instance);

        /** @Then the collaborator is instantiated */
        self::assertInstanceOf(ZonedShift::class, $instance);
    }
}
