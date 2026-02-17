<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeImmutable;
use TinyBlocks\Time\Internal\Exceptions\InvalidInstant;
use TinyBlocks\Time\Internal\TextDecoder;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a single point on the timeline, always normalized to UTC with microsecond precision.
 */
final readonly class Instant implements ValueObject
{
    use ValueObjectBehavior;

    private const string UNIX_FORMAT = 'U';
    private const string ISO8601_FORMAT = 'Y-m-d\TH:i:sP';
    private const string MICROSECOND_FORMAT = 'U.u';

    private function __construct(private DateTimeImmutable $datetime)
    {
    }

    /**
     * Creates an Instant representing the current moment in UTC with microsecond precision.
     *
     * @return Instant The current Instant, normalized to UTC.
     */
    public static function now(): Instant
    {
        $utc = Timezone::utc()->toDateTimeZone();
        $datetime = DateTimeImmutable::createFromFormat(
            self::MICROSECOND_FORMAT,
            sprintf('%.6F', microtime(true)),
            $utc
        );

        /** @var DateTimeImmutable $datetime */
        return new Instant(datetime: $datetime->setTimezone($utc));
    }

    /**
     * Creates an Instant by decoding a date-time string.
     *
     * @param string $value A date-time string in a supported format (e.g. 2026-02-17T10:30:00+00:00).
     * @return Instant The created Instant, normalized to UTC.
     * @throws InvalidInstant If the value cannot be decoded into a valid instant.
     */
    public static function fromString(string $value): Instant
    {
        $decoder = TextDecoder::create();
        $datetime = $decoder->decode(value: $value);

        return new Instant(datetime: $datetime);
    }

    /**
     * Creates an Instant from a Unix timestamp in seconds.
     *
     * @param int $seconds The number of seconds since the Unix epoch (1970-01-01T00:00:00Z).
     * @return Instant The created Instant, normalized to UTC.
     */
    public static function fromUnixSeconds(int $seconds): Instant
    {
        $utc = Timezone::utc()->toDateTimeZone();
        $datetime = DateTimeImmutable::createFromFormat(self::UNIX_FORMAT, (string)$seconds, $utc);

        /** @var DateTimeImmutable $datetime */
        return new Instant(datetime: $datetime->setTimezone($utc));
    }

    /**
     * Formats this instant as an ISO 8601 string in UTC (e.g. 2026-02-17T10:30:00+00:00).
     *
     * @return string The ISO 8601 representation without fractional seconds.
     */
    public function toIso8601(): string
    {
        return $this->datetime->format(self::ISO8601_FORMAT);
    }

    /**
     * Returns the number of seconds since the Unix epoch.
     *
     * @return int The Unix timestamp in seconds.
     */
    public function toUnixSeconds(): int
    {
        return $this->datetime->getTimestamp();
    }

    /**
     * Returns the underlying DateTimeImmutable instance in UTC.
     *
     * @return DateTimeImmutable The UTC date-time with microsecond precision.
     */
    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->datetime;
    }
}
