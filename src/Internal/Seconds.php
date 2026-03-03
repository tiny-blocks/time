<?php

declare(strict_types=1);

namespace TinyBlocks\Time\Internal;

use TinyBlocks\Time\Internal\Exceptions\InvalidSeconds;

/**
 * Represents a non-negative quantity of time in seconds.
 * Internal building block used by Duration and other time types.
 */
final readonly class Seconds
{
    private const int ZERO = 0;

    private function __construct(public int $value)
    {
    }

    public static function zero(): Seconds
    {
        return new Seconds(value: self::ZERO);
    }

    public static function from(int $value): Seconds
    {
        if ($value < self::ZERO) {
            throw InvalidSeconds::becauseIsNegative(value: $value);
        }

        return new Seconds(value: $value);
    }

    public function plus(Seconds $other): Seconds
    {
        return new Seconds(value: $this->value + $other->value);
    }

    public function minus(Seconds $other): Seconds
    {
        $result = $this->value - $other->value;

        if ($result < self::ZERO) {
            throw InvalidSeconds::becauseResultIsNegative(current: $this->value, subtracted: $other->value);
        }

        return new Seconds(value: $result);
    }

    public function divide(Seconds $other): int
    {
        if ($other->isZero()) {
            throw InvalidSeconds::becauseDivisorIsZero();
        }

        return intdiv($this->value, $other->value);
    }

    public function isZero(): bool
    {
        return $this->value === self::ZERO;
    }

    public function isGreaterThan(Seconds $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(Seconds $other): bool
    {
        return $this->value < $other->value;
    }

    public function divideByScalar(int $divisor): int
    {
        return intdiv($this->value, $divisor);
    }
}
