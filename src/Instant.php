<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeImmutable;
use TinyBlocks\Mapper\ScalarCodec;
use TinyBlocks\Time\Exceptions\InvalidInstant;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;
use TinyBlocks\Time\Internal\TextDecoder;
use TinyBlocks\Time\Internal\ZonedShift;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a single point on the timeline, always normalized to UTC with microsecond precision.
 */
#[ScalarCodec(decode: 'fromString', encode: 'toIso8601')]
final readonly class Instant implements ValueObject
{
    use ValueObjectBehavior;

    private const string UNIX_FORMAT = 'U';
    private const string OFFSET_FORMAT = 'P';
    private const string ISO8601_FORMAT = 'Y-m-d\TH:i:sP';
    private const string ISO8601_MICRO_FORMAT = 'Y-m-d\TH:i:s.uP';
    private const string ISO8601_DATETIME_FORMAT = 'Y-m-d\TH:i:s';
    private const string FRACTIONAL_SECONDS_FORMAT = 'u';

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

        return new Instant(datetime: new DateTimeImmutable(timezone: $utc));
    }

    /**
     * Creates an Instant from a date-time string.
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

        return new Instant(datetime: $datetime->setTimezone($utc));
    }

    /**
     * Returns a new Instant shifted forward by the given Duration.
     *
     * @param Duration $duration The amount of time to add.
     * @return Instant A new Instant shifted forward in time.
     */
    public function plus(Duration $duration): Instant
    {
        $template = '+%d seconds';
        $modified = $this->datetime->modify(sprintf($template, $duration->toSeconds()));

        return new Instant(datetime: $modified);
    }

    /**
     * Returns a new Instant shifted backward by the given Duration.
     *
     * @param Duration $duration The amount of time to subtract.
     * @return Instant A new Instant shifted backward in time.
     */
    public function minus(Duration $duration): Instant
    {
        $template = '-%d seconds';
        $modified = $this->datetime->modify(sprintf($template, $duration->toSeconds()));

        return new Instant(datetime: $modified);
    }

    /**
     * Tells whether this instant is strictly after the other.
     *
     * @param Instant $other The instant to compare against.
     * @return bool True if this instant follows the other.
     */
    public function isAfter(Instant $other): bool
    {
        return $this->datetime > $other->datetime;
    }

    /**
     * Tells whether this instant is strictly before the other.
     *
     * @param Instant $other The instant to compare against.
     * @return bool True if this instant precedes the other.
     */
    public function isBefore(Instant $other): bool
    {
        return $this->datetime < $other->datetime;
    }

    /**
     * Returns a new Instant with the given number of years added, re-anchored in a timezone.
     *
     * <p>A negative count shifts backward. The instant is projected into <code>$zone</code> (UTC
     * when omitted), the year shift runs on the local date through {@see LocalDate::plusYears()}
     * with the same February 29 clamping, and the result is normalized back to UTC.</p>
     *
     * @param int $years The number of years to add (may be negative).
     * @param Timezone|null $zone The timezone used to re-anchor the shift. Defaults to UTC.
     * @return Instant A new Instant re-anchored after the year shift, normalized to UTC.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function plusYears(int $years, ?Timezone $zone = null): Instant
    {
        $timezone = ($zone ?? Timezone::utc());
        $shiftedDate = $this->toLocalDate(zone: $timezone)->plusYears(years: $years);
        $recomposed = ZonedShift::recompose(zone: $timezone, original: $this->datetime, shiftedDate: $shiftedDate);

        return new Instant(datetime: $recomposed);
    }

    /**
     * Returns the Instant as an ISO 8601 string in UTC at the chosen sub-second precision.
     *
     * <p>The output always carries the +00:00 offset and is composed of a date, a time, and an
     * optional fractional-seconds component determined by the precision argument:</p>
     *
     * <ul>
     * <li>Precision::Seconds (default): no fractional component, e.g. 2026-02-17T10:30:00+00:00.</li>
     * <li>Precision::Milliseconds: three fractional digits, e.g. 2026-02-17T08:27:21.106+00:00.</li>
     * <li>Precision::Microseconds: six fractional digits, e.g. 2026-02-17T08:27:21.106011+00:00.</li>
     * </ul>
     *
     * <p>Use Microseconds when interoperating with stores that hold sub-second precision (e.g. a
     * TIMESTAMP(6) column). Use Seconds for human-facing logs or APIs that do not carry sub-second
     * timing. Use Milliseconds when consumers expect millisecond resolution but not microseconds
     * (some web APIs, JavaScript Date).</p>
     *
     * @param Precision $precision The sub-second granularity to include in the output.
     *                             Defaults to Precision::Seconds.
     * @return string The ISO 8601 representation in UTC at the requested precision.
     */
    public function toIso8601(Precision $precision = Precision::Seconds): string
    {
        $template = '%s.%s%s';

        return match ($precision) {
            Precision::Seconds      => $this->datetime->format(self::ISO8601_FORMAT),
            Precision::Microseconds => $this->datetime->format(self::ISO8601_MICRO_FORMAT),
            Precision::Milliseconds => sprintf(
                $template,
                $this->datetime->format(self::ISO8601_DATETIME_FORMAT),
                substr($this->datetime->format(self::FRACTIONAL_SECONDS_FORMAT), 0, 3),
                $this->datetime->format(self::OFFSET_FORMAT)
            )
        };
    }

    /**
     * Returns a new Instant with the given number of years subtracted, re-anchored in a timezone.
     *
     * <p>A negative count shifts forward. Delegates to {@see Instant::plusYears()} with the sign
     * inverted, so February 29 shifted onto a common year is clamped to February 28 and the same
     * re-anchoring applies.</p>
     *
     * @param int $years The number of years to subtract (may be negative).
     * @param Timezone|null $zone The timezone used to re-anchor the shift. Defaults to UTC.
     * @return Instant A new Instant re-anchored after the year shift, normalized to UTC.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function minusYears(int $years, ?Timezone $zone = null): Instant
    {
        return $this->plusYears(years: -$years, zone: $zone);
    }

    /**
     * Returns a new Instant with the given number of months added, re-anchored in a timezone.
     *
     * <p>A negative count shifts backward. The instant is projected into <code>$zone</code> (UTC
     * when omitted). The calendar shift runs on the local date through {@see LocalDate::plusMonths()},
     * which clamps the day to the last valid day of the target month. The original local time of
     * day, including microseconds, is preserved, and the result is normalized back to UTC.</p>
     *
     * <p>When the resulting local wall time does not exist in the zone because of a spring-forward
     * gap, PHP's own resolution is adopted, shifting the time forward by the gap. For example, an
     * instant whose local time is 2026-02-08 02:30 in America/New_York, plus 1 month, targets the
     * non-existent local 2026-03-08 02:30 and resolves to 2026-03-08 03:30 -04:00, which is the
     * instant 2026-03-08T07:30:00+00:00.</p>
     *
     * @param int $months The number of months to add (may be negative).
     * @param Timezone|null $zone The timezone used to re-anchor the shift. Defaults to UTC.
     * @return Instant A new Instant re-anchored after the month shift, normalized to UTC.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function plusMonths(int $months, ?Timezone $zone = null): Instant
    {
        $timezone = ($zone ?? Timezone::utc());
        $shiftedDate = $this->toLocalDate(zone: $timezone)->plusMonths(months: $months);
        $recomposed = ZonedShift::recompose(zone: $timezone, original: $this->datetime, shiftedDate: $shiftedDate);

        return new Instant(datetime: $recomposed);
    }

    /**
     * Returns a new Instant with the given number of months subtracted, re-anchored in a timezone.
     *
     * <p>A negative count shifts forward. Delegates to {@see Instant::plusMonths()} with the sign
     * inverted, so the same re-anchoring, day clamping, and spring-forward gap resolution apply.</p>
     *
     * @param int $months The number of months to subtract (may be negative).
     * @param Timezone|null $zone The timezone used to re-anchor the shift. Defaults to UTC.
     * @return Instant A new Instant re-anchored after the month shift, normalized to UTC.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function minusMonths(int $months, ?Timezone $zone = null): Instant
    {
        return $this->plusMonths(months: -$months, zone: $zone);
    }

    /**
     * Projects this instant into a calendar date under the given timezone.
     *
     * @param Timezone $zone The timezone used to determine the civil date.
     * @return LocalDate The local date in the given timezone at this instant.
     * @throws InvalidLocalDate If the projected date falls outside the range 0001 to 9999.
     */
    public function toLocalDate(Timezone $zone): LocalDate
    {
        $datetime = $this->datetime->setTimezone($zone->toDateTimeZone());

        return LocalDate::fromString(value: $datetime->format('Y-m-d'));
    }

    /**
     * Returns the Duration between this instant and another.
     * The result is always non-negative (absolute distance).
     *
     * @param Instant $other The instant to measure the distance to.
     * @return Duration The absolute duration between the two instants.
     */
    public function durationUntil(Instant $other): Duration
    {
        $difference = abs($this->datetime->getTimestamp() - $other->datetime->getTimestamp());

        return Duration::fromSeconds(seconds: $difference);
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
     * Tells whether this instant is after or at the same moment as the other.
     *
     * @param Instant $other The instant to compare against.
     * @return bool True if this instant is at or after the other.
     */
    public function isAfterOrEqual(Instant $other): bool
    {
        return $this->datetime >= $other->datetime;
    }

    /**
     * Tells whether this instant is before or at the same moment as the other.
     *
     * @param Instant $other The instant to compare against.
     * @return bool True if this instant is at or before the other.
     */
    public function isBeforeOrEqual(Instant $other): bool
    {
        return $this->datetime <= $other->datetime;
    }

    /**
     * Returns the Instant as a DateTimeImmutable in UTC.
     *
     * @return DateTimeImmutable The UTC date-time with microsecond precision.
     */
    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->datetime;
    }
}
