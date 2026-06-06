<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Models;

use TinyBlocks\Time\Instant;

final readonly class Event
{
    public function __construct(public Instant $occurredAt, public string $name)
    {
    }
}
