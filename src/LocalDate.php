<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeImmutable;
use TinyBlocks\Mapper\ScalarCodec;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a calendar date (year, month, day) without time and without timezone.
 * Two instances of this type for the same date are always equal by value.
 */
#[ScalarCodec(decode: 'fromString', encode: 'toIso8601')]
final readonly class LocalDate implements ValueObject
{
    use ValueObjectBehavior;

    private const int MAX_YEAR = 9999;
    private const int MIN_YEAR = 1;
    private const string DATE_FORMAT = 'Y-m-d';
    private const string DATE_PATTERN = '/^\d{4}-\d{2}-\d{2}$/';

    private function __construct(private DateTimeImmutable $date)
    {
    }

    /**
     * Creates a LocalDate from explicit year, month, and day components.
     *
     * @param int $year The calendar year (1–9999).
     * @param int $month The month of the year (1–12).
     * @param int $day The day of the month (1–31, validated per month and leap year).
     * @return LocalDate The created local date.
     * @throws InvalidLocalDate If the combination does not form a valid calendar date.
     */
    public static function of(int $year, int $month, int $day): LocalDate
    {
        if ($year < self::MIN_YEAR || $year > self::MAX_YEAR || !checkdate($month, $day, $year)) {
            throw InvalidLocalDate::becauseComponentsAreInvalid(year: $year, month: $month, day: $day);
        }

        $template = '%04d-%02d-%02d';

        return LocalDate::fromString(value: sprintf($template, $year, $month, $day));
    }

    /**
     * Creates a LocalDate representing today in the given timezone.
     *
     * @param Timezone $zone The timezone used to determine the current civil date.
     * @return LocalDate Today's local date in the given timezone.
     */
    public static function today(Timezone $zone): LocalDate
    {
        $dateTime = new DateTimeImmutable(datetime: 'now', timezone: $zone->toDateTimeZone());

        return LocalDate::fromString(value: $dateTime->format(self::DATE_FORMAT));
    }

    /**
     * Creates a LocalDate by parsing an ISO 8601 date string (YYYY-MM-DD).
     *
     * @param string $value A date string in YYYY-MM-DD format (e.g. "2026-05-23").
     * @return LocalDate The created local date.
     * @throws InvalidLocalDate If the string is malformed or encodes an invalid date.
     */
    public static function fromString(string $value): LocalDate
    {
        if (preg_match(self::DATE_PATTERN, $value) !== 1) {
            throw InvalidLocalDate::becauseValueIsInvalid(value: $value);
        }

        $utc = Timezone::utc()->toDateTimeZone();
        $parsed = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $value, $utc);

        if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
            throw InvalidLocalDate::becauseValueIsInvalid(value: $value);
        }

        return new LocalDate(date: $parsed);
    }

    /**
     * Returns the year.
     *
     * @return int The four-digit calendar year.
     */
    public function year(): int
    {
        return (int)$this->date->format('Y');
    }

    /**
     * Returns the month of the year.
     *
     * @return int The month (1–12).
     */
    public function month(): int
    {
        return (int)$this->date->format('n');
    }

    /**
     * Tells whether this date is strictly after another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date follows the other.
     */
    public function isAfter(LocalDate $other): bool
    {
        return $this->date > $other->date;
    }

    /**
     * Tells whether this date is strictly before another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date precedes the other.
     */
    public function isBefore(LocalDate $other): bool
    {
        return $this->date < $other->date;
    }

    /**
     * Returns a copy of this date shifted forward by the given number of days.
     * A negative count shifts backward.
     *
     * @param int $days The number of days to add (may be negative).
     * @return LocalDate A new LocalDate shifted forward by the given count.
     */
    public function plusDays(int $days): LocalDate
    {
        $template = '%+d days';
        $modified = $this->date->modify(sprintf($template, $days));

        return new LocalDate(date: $modified);
    }

    /**
     * Returns the day of the week for this date.
     *
     * @return DayOfWeek The day of the week (Monday through Sunday).
     */
    public function dayOfWeek(): DayOfWeek
    {
        return DayOfWeek::from((int)$this->date->format('N'));
    }

    /**
     * Returns a copy of this date shifted backward by the given number of days.
     * A negative count shifts forward.
     *
     * @param int $days The number of days to subtract (may be negative).
     * @return LocalDate A new LocalDate shifted backward by the given count.
     */
    public function minusDays(int $days): LocalDate
    {
        $template = '%+d days';
        $modified = $this->date->modify(sprintf($template, -$days));

        return new LocalDate(date: $modified);
    }

    /**
     * Returns the LocalDate as an ISO 8601 date string (YYYY-MM-DD).
     *
     * @return string The date in YYYY-MM-DD format (e.g. "2026-05-23").
     */
    public function toIso8601(): string
    {
        return $this->date->format(self::DATE_FORMAT);
    }

    /**
     * Returns the day of the month.
     *
     * @return int The day (1–31).
     */
    public function dayOfMonth(): int
    {
        return (int)$this->date->format('j');
    }

    /**
     * Tells whether this date is after or equal to another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date is at or after the other.
     */
    public function isAfterOrEqual(LocalDate $other): bool
    {
        return $this->date >= $other->date;
    }

    /**
     * Tells whether this date is before or equal to another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date is at or before the other.
     */
    public function isBeforeOrEqual(LocalDate $other): bool
    {
        return $this->date <= $other->date;
    }
}
