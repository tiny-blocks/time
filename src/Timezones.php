<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use Countable;
use TinyBlocks\Collection\Collectible;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\KeyPreservation;
use TinyBlocks\Time\Exceptions\InvalidTimezone;

/**
 * Immutable collection of Timezone objects.
 */
final readonly class Timezones implements Countable
{
    private function __construct(private Collectible $timezones)
    {
    }

    /**
     * Creates a Timezones from Timezone objects.
     *
     * @param Timezone ...$timezones One or more Timezone instances.
     * @return Timezones The created collection.
     */
    public static function from(Timezone ...$timezones): Timezones
    {
        return new Timezones(timezones: Collection::createFrom(elements: $timezones));
    }

    /**
     * Creates a Timezones from IANA identifier strings.
     *
     * @param string ...$identifiers One or more IANA timezone identifiers (e.g. America/Sao_Paulo).
     * @return Timezones The created collection.
     * @throws InvalidTimezone If any identifier is not a valid IANA timezone.
     */
    public static function fromStrings(string ...$identifiers): Timezones
    {
        $timezones = Collection::createFrom(elements: $identifiers)
            ->map(transformations: static fn(string $identifier): Timezone => Timezone::from(identifier: $identifier));

        return new Timezones(timezones: Collection::createFrom(elements: $timezones));
    }

    /**
     * Returns all Timezone objects in this collection.
     *
     * @return list<Timezone> The list of all Timezone objects.
     */
    public function all(): array
    {
        return [...$this->timezones];
    }

    /**
     * Returns the number of timezones in this collection.
     *
     * @return int The total count.
     */
    public function count(): int
    {
        return $this->timezones->count();
    }

    /**
     * Checks whether the given IANA identifier exists in this collection.
     *
     * @param string $iana The IANA timezone identifier to check (e.g. America/New_York).
     * @return bool True if the identifier exists in this collection, false otherwise.
     */
    public function contains(string $iana): bool
    {
        return $this->findByIdentifier(iana: $iana) !== null;
    }

    /**
     * Returns the Timezones as strings.
     *
     * @return list<string> The list of IANA timezone identifier strings.
     */
    public function toStrings(): array
    {
        return $this->timezones
            ->map(transformations: static fn(Timezone $timezone): string => $timezone->toString())
            ->toArray(keyPreservation: KeyPreservation::DISCARD);
    }

    /**
     * Finds a Timezone by its IANA identifier.
     *
     * @param string $iana The IANA timezone identifier to search for (e.g. America/Sao_Paulo).
     * @return Timezone|null The matching Timezone, or null if not found.
     */
    public function findByIdentifier(string $iana): ?Timezone
    {
        return $this->timezones->findBy(static fn(Timezone $timezone): bool => $timezone->value === $iana);
    }

    /**
     * Finds a Timezone by its IANA identifier, falling back to UTC if not found.
     *
     * @param string $iana The IANA timezone identifier to search for (e.g. America/Sao_Paulo).
     * @return Timezone The matching Timezone, or UTC if not found in this collection.
     */
    public function findByIdentifierOrUtc(string $iana): Timezone
    {
        return $this->findByIdentifier(iana: $iana) ?? Timezone::utc();
    }
}
