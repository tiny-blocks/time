<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Unit;

use TinyBlocks\Time\MonotonicClock;

final class MonotonicClockFake implements MonotonicClock
{
    private int $cursor = 0;

    private readonly array $readings;

    public function __construct(int ...$readings)
    {
        $this->readings = $readings;
    }

    public function nanoseconds(): int
    {
        $reading = $this->readings[$this->cursor];
        $this->cursor++;

        return $reading;
    }
}
