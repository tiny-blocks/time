<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Time\Models;

use TinyBlocks\Time\LocalDate;

final readonly class Holiday
{
    public function __construct(public LocalDate $date, public string $name)
    {
    }
}
