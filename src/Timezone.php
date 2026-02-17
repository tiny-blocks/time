<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeZone;
use TinyBlocks\Time\Internal\Exceptions\InvalidTimezone;

/**
 * Represents a single IANA timezone identifier (e.g. America/Sao_Paulo).
 */
final readonly class Timezone
{
    public string $value;

    private function __construct(string $identifier)
    {
        if (empty($identifier) || !in_array($identifier, self::allIdentifiers(), true)) {
            throw new InvalidTimezone(identifier: $identifier);
        }

        $this->value = $identifier;
    }

    /**
     * Creates a Timezone representing UTC.
     *
     * @return Timezone The UTC Timezone instance.
     */
    public static function utc(): Timezone
    {
        return new Timezone(identifier: 'UTC');
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
        return new Timezone(identifier: $identifier);
    }

    /**
     * Returns the IANA timezone identifier as a string.
     *
     * @return string The IANA timezone identifier.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Converts this Timezone to a DateTimeZone instance.
     *
     * @return DateTimeZone The corresponding DateTimeZone.
     */
    public function toDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone($this->value);
    }

    /**
     * @return list<string>
     */
    private static function allIdentifiers(): array
    {
        /** @var list<string>|null $identifiers */
        static $identifiers = null;

        return $identifiers ??= DateTimeZone::listIdentifiers();
    }
}
