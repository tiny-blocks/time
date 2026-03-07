<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use TinyBlocks\Time\Internal\Exceptions\InvalidTimeOfDay;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a time of day (hour and minute) without date or timezone context.
 * Values range from 00:00 to 23:59.
 */
final readonly class TimeOfDay implements ValueObject
{
    use ValueObjectBehavior;

    private const int MAX_HOUR = 23;
    private const int MAX_MINUTE = 59;
    private const int MINUTES_PER_HOUR = 60;

    private const string PATTERN = '/^(?P<hour>\d{2}):(?P<minute>\d{2})(?::(?:\d{2}))?$/';

    private function __construct(public int $hour, public int $minute)
    {
    }

    /**
     * Creates a TimeOfDay from hour and minute components.
     *
     * @param int $hour The hour (0-23).
     * @param int $minute The minute (0-59).
     * @return TimeOfDay The created time of day.
     * @throws InvalidTimeOfDay If hour or minute is out of range.
     */
    public static function from(int $hour, int $minute): TimeOfDay
    {
        if ($hour < 0 || $hour > self::MAX_HOUR) {
            throw InvalidTimeOfDay::becauseHourIsOutOfRange(hour: $hour);
        }

        if ($minute < 0 || $minute > self::MAX_MINUTE) {
            throw InvalidTimeOfDay::becauseMinuteIsOutOfRange(minute: $minute);
        }

        return new TimeOfDay(hour: $hour, minute: $minute);
    }

    /**
     * Creates a TimeOfDay from a string in "HH:MM" or "HH:MM:SS" format.
     * When seconds are present, they are discarded.
     *
     * @param string $value The time string (e.g. "08:30", "14:00", "08:30:00").
     * @return TimeOfDay The created time of day.
     * @throws InvalidTimeOfDay If the format is invalid or values are out of range.
     */
    public static function fromString(string $value): TimeOfDay
    {
        if (preg_match(self::PATTERN, $value, $matches) !== 1) {
            throw InvalidTimeOfDay::becauseFormatIsInvalid(value: $value);
        }

        return self::from(hour: (int)$matches['hour'], minute: (int)$matches['minute']);
    }

    /**
     * Derives the time of day from an Instant (in UTC).
     *
     * @param Instant $instant The point in time to extract the time from.
     * @return TimeOfDay The corresponding time of day.
     */
    public static function fromInstant(Instant $instant): TimeOfDay
    {
        $dateTime = $instant->toDateTimeImmutable();

        return new TimeOfDay(
            hour: (int)$dateTime->format('G'),
            minute: (int)$dateTime->format('i')
        );
    }

    /**
     * Creates a TimeOfDay representing noon (12:00).
     *
     * @return TimeOfDay Noon.
     */
    public static function noon(): TimeOfDay
    {
        return new TimeOfDay(hour: 12, minute: 0);
    }

    /**
     * Creates a TimeOfDay representing midnight (00:00).
     *
     * @return TimeOfDay Midnight.
     */
    public static function midnight(): TimeOfDay
    {
        return new TimeOfDay(hour: 0, minute: 0);
    }

    /**
     * Returns the total number of minutes since midnight.
     *
     * @return int Minutes since 00:00.
     */
    public function toMinutesSinceMidnight(): int
    {
        return ($this->hour * self::MINUTES_PER_HOUR) + $this->minute;
    }

    /**
     * Returns the Duration from midnight to this time of day.
     *
     * @return Duration The duration since midnight.
     */
    public function toDuration(): Duration
    {
        return Duration::fromMinutes(minutes: $this->toMinutesSinceMidnight());
    }

    /**
     * Returns true if this time is strictly before another.
     *
     * @param TimeOfDay $other The time to compare against.
     * @return bool True if this time precedes the other.
     */
    public function isBefore(TimeOfDay $other): bool
    {
        return $this->toMinutesSinceMidnight() < $other->toMinutesSinceMidnight();
    }

    /**
     * Returns true if this time is strictly after another.
     *
     * @param TimeOfDay $other The time to compare against.
     * @return bool True if this time follows the other.
     */
    public function isAfter(TimeOfDay $other): bool
    {
        return $this->toMinutesSinceMidnight() > $other->toMinutesSinceMidnight();
    }

    /**
     * Returns true if this time is before or equal to another.
     *
     * @param TimeOfDay $other The time to compare against.
     * @return bool True if this time is at or before the other.
     */
    public function isBeforeOrEqual(TimeOfDay $other): bool
    {
        return $this->toMinutesSinceMidnight() <= $other->toMinutesSinceMidnight();
    }

    /**
     * Returns true if this time is after or equal to another.
     *
     * @param TimeOfDay $other The time to compare against.
     * @return bool True if this time is at or after the other.
     */
    public function isAfterOrEqual(TimeOfDay $other): bool
    {
        return $this->toMinutesSinceMidnight() >= $other->toMinutesSinceMidnight();
    }

    /**
     * Returns the Duration between this time and another.
     * The other time must be after this time.
     *
     * @param TimeOfDay $other The later time of day.
     * @return Duration The duration between the two times.
     * @throws InvalidTimeOfDay If the other time is not after this time.
     */
    public function durationUntil(TimeOfDay $other): Duration
    {
        $diff = $other->toMinutesSinceMidnight() - $this->toMinutesSinceMidnight();

        if ($diff <= 0) {
            throw InvalidTimeOfDay::becauseEndIsNotAfterStart(from: $this, to: $other);
        }

        return Duration::fromMinutes(minutes: $diff);
    }

    /**
     * Formats this time as "HH:MM".
     *
     * @return string The formatted time string.
     */
    public function toString(): string
    {
        return sprintf('%02d:%02d', $this->hour, $this->minute);
    }
}
