<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use Countable;
use TinyBlocks\Time\Internal\Exceptions\InvalidTimezone;

/**
 * Immutable collection of Timezone objects.
 */
final readonly class Timezones implements Countable
{
    /** @var list<Timezone> */
    private array $items;

    /**
     * @param list<Timezone> $items
     */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Creates a collection from Timezone objects.
     *
     * @param Timezone ...$timezones One or more Timezone instances.
     * @return Timezones The created collection.
     */
    public static function from(Timezone ...$timezones): Timezones
    {
        return new Timezones(items: $timezones);
    }

    /**
     * Creates a collection from IANA identifier strings.
     *
     * @param string ...$identifiers One or more IANA timezone identifiers (e.g. America/Sao_Paulo).
     * @return Timezones The created collection.
     * @throws InvalidTimezone If any identifier is not a valid IANA timezone.
     */
    public static function fromStrings(string ...$identifiers): Timezones
    {
        $items = array_map(
            static fn(string $identifier): Timezone => Timezone::from(identifier: $identifier),
            $identifiers
        );

        return new Timezones(items: $items);
    }

    /**
     * Returns all Timezone objects in this collection.
     *
     * @return list<Timezone> The list of all Timezone objects.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Returns the number of timezones in this collection.
     *
     * @return int The total count.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Checks whether the given IANA identifier exists in this collection.
     *
     * @param string $iana The IANA timezone identifier to check (e.g. America/New_York).
     * @return bool True if the identifier exists in this collection, false otherwise.
     */
    public function contains(string $iana): bool
    {
        return array_any(
            $this->items,
            static fn(Timezone $timezone): bool => $timezone->value === $iana
        );
    }

    /**
     * Finds a Timezone by its IANA identifier.
     *
     * @param string $iana The IANA timezone identifier to search for (e.g. America/Sao_Paulo).
     * @return Timezone|null The matching Timezone, or null if not found.
     */
    public function findByIdentifier(string $iana): ?Timezone
    {
        return array_find(
            $this->items,
            static fn(Timezone $timezone): bool => $timezone->value === $iana
        );
    }

    /**
     * Returns all timezone identifiers as plain strings.
     *
     * @return list<string> The list of IANA timezone identifier strings.
     */
    public function toStrings(): array
    {
        return array_map(
            static fn(Timezone $timezone): string => $timezone->toString(),
            $this->items
        );
    }
}
