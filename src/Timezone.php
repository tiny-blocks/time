<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeZone;
use TinyBlocks\Time\Exceptions\InvalidTimezone;

/**
 * Represents a single IANA timezone identifier (e.g. America/Sao_Paulo).
 */
final readonly class Timezone
{
    private function __construct(public string $value)
    {
        if ($this->value === '' || !in_array($this->value, DateTimeZone::listIdentifiers(), true)) {
            throw InvalidTimezone::becauseIdentifierIsInvalid(identifier: $this->value);
        }
    }

    /**
     * Creates a Timezone representing UTC.
     *
     * @return Timezone The UTC Timezone instance.
     */
    public static function utc(): Timezone
    {
        return new Timezone(value: 'UTC');
    }

    /**
     * Creates a Timezone from a valid IANA identifier.
     *
     * @param string $identifier The IANA timezone identifier (e.g. America/Sao_Paulo).
     * @return Timezone The created Timezone instance.
     * @throws InvalidTimezone If the identifier is not a valid IANA timezone.
     */
    public static function from(string $identifier): Timezone
    {
        return new Timezone(value: $identifier);
    }

    /**
     * Returns the Timezone as a string.
     *
     * @return string The IANA timezone identifier.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Returns the Timezone as a DateTimeZone.
     *
     * @return DateTimeZone The corresponding DateTimeZone.
     */
    public function toDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone(timezone: $this->value);
    }
}
