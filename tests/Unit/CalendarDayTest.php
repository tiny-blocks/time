<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\Time\Internal\CalendarDay;

final class CalendarDayTest extends TestCase
{
    public function testConstructorWhenInvokedViaReflectionThenInstanceIsCreated(): void
    {
        /** @Given an instance allocated without invoking the private constructor */
        $instance = new ReflectionClass(CalendarDay::class)->newInstanceWithoutConstructor();

        /** @When the private constructor of the static-only collaborator is invoked through reflection */
        new ReflectionMethod(CalendarDay::class, '__construct')->invoke($instance);

        /** @Then the collaborator is instantiated */
        self::assertInstanceOf(CalendarDay::class, $instance);
    }
}
