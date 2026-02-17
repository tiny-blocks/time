<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal\Exceptions;

use InvalidArgumentException;

final class InvalidInstant extends InvalidArgumentException
{
    public function __construct(private readonly string $value)
    {
        $template = 'The value <%s> could not be decoded into a valid instant.';

        parent::__construct(message: sprintf($template, $this->value));
    }
}
