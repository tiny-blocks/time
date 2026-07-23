<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents an elapsed interval measured by a {@see MonotonicClock}, expressed in nanoseconds.
 *
 * <p>Distinct from the wall-clock {@see Duration}: an Elapsed is the delta between two readings
 * on the same monotonic clock and carries no calendar meaning. Construction normally happens
 * through {@see Stopwatch}. The {@see Elapsed::fromNanoseconds} factory exists for callers that
 * already hold a precomputed delta.</p>
 */
final readonly class Elapsed implements ValueObject
{
    use ValueObjectBehavior;

    private const int NANOSECONDS_PER_MILLISECOND = 1_000_000;

    private function __construct(public int $nanoseconds)
    {
    }

    /**
     * Creates an Elapsed from a precomputed nanosecond count.
     *
     * @param int $nanoseconds The elapsed interval expressed in nanoseconds.
     * @return Elapsed The created Elapsed instance.
     */
    public static function fromNanoseconds(int $nanoseconds): Elapsed
    {
        return new Elapsed(nanoseconds: $nanoseconds);
    }

    /**
     * Returns the Elapsed as a millisecond count rounded to two decimal places.
     *
     * @return float The elapsed interval in milliseconds.
     */
    public function toMilliseconds(): float
    {
        return round(($this->nanoseconds / self::NANOSECONDS_PER_MILLISECOND), 2);
    }
}
