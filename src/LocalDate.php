<?php

declare(strict_types=1);

namespace TinyBlocks\Time;

use DateTimeImmutable;
use TinyBlocks\Mapper\ScalarCodec;
use TinyBlocks\Time\Exceptions\InvalidLocalDate;
use TinyBlocks\Time\Internal\CalendarDay;
use TinyBlocks\Time\Internal\IsoDate;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectBehavior;

/**
 * Represents a calendar date (year, month, day) without time and without timezone.
 *
 * <p>Two instances of this type for the same date are always equal by value, and their ordering
 * depends on the date alone. The state is the canonical text rather than a date-time object for
 * both reasons: structural equality compares a property that is not itself a value object by
 * identity, and a date-time object parsed from a date carries the wall clock of the moment it was
 * parsed, which made two instances of the same date order as if one preceded the other.</p>
 */
#[ScalarCodec(decode: 'fromString', encode: 'toIso8601')]
final readonly class LocalDate implements ValueObject
{
    use ValueObjectBehavior;

    private const int MAX_YEAR = 9999;
    private const int MIN_YEAR = 1;
    private const string DATE_FORMAT = 'Y-m-d';
    private const string DATE_PATTERN = '/^\d{4}-\d{2}-\d{2}$/';

    private function __construct(private string $canonical)
    {
    }

    /**
     * Creates a LocalDate from explicit year, month, and day components.
     *
     * @param int $year The calendar year (1-9999).
     * @param int $month The month of the year (1-12).
     * @param int $day The day of the month (1-31, validated per month and leap year).
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
        $dateTime = new DateTimeImmutable(timezone: $zone->toDateTimeZone());

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

        return new LocalDate(canonical: IsoDate::canonicalize(datetime: $parsed));
    }

    /**
     * Returns the year.
     *
     * @return int The four-digit calendar year.
     */
    public function year(): int
    {
        return (int)IsoDate::restore(canonical: $this->canonical)->format('Y');
    }

    /**
     * Returns the month of the year.
     *
     * @return int The month (1-12).
     */
    public function month(): int
    {
        return (int)IsoDate::restore(canonical: $this->canonical)->format('n');
    }

    /**
     * Combines this date with a time of day, read in the given timezone, into an Instant.
     *
     * <p>This is the inverse of {@see Instant::toLocalDate()}. The date and the time are civil values,
     * so the timezone is what anchors them to a point on the timeline.</p>
     *
     * <p>Daylight saving leaves two civil times that are not one-to-one with the timeline, and both
     * resolve deterministically. A time inside the spring-forward gap does not exist, and it maps to the
     * same instant as the first civil time after the gap. A time inside the fall-back overlap happens
     * twice, and it maps to the earlier of the two, the one still on the pre-transition offset.</p>
     *
     * @param TimeOfDay $time The civil time of day to combine with this date.
     * @param Timezone $zone The timezone the civil date and time are read in.
     * @return Instant The instant those civil values denote in that timezone.
     */
    public function atTime(TimeOfDay $time, Timezone $zone): Instant
    {
        $template = '%s %02d:%02d:00';
        $civil = sprintf($template, $this->toIso8601(), $time->hour, $time->minute);

        $datetime = new DateTimeImmutable(datetime: $civil, timezone: $zone->toDateTimeZone());

        return Instant::fromUnixSeconds(seconds: $datetime->getTimestamp());
    }

    /**
     * Tells whether this date is strictly after another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date follows the other.
     */
    public function isAfter(LocalDate $other): bool
    {
        return IsoDate::restore(canonical: $this->canonical) > IsoDate::restore(canonical: $other->canonical);
    }

    /**
     * Tells whether this date is strictly before another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date precedes the other.
     */
    public function isBefore(LocalDate $other): bool
    {
        return IsoDate::restore(canonical: $this->canonical) < IsoDate::restore(canonical: $other->canonical);
    }

    /**
     * Returns a copy of this date shifted forward by the given number of days.
     *
     * <p>A negative count shifts backward.</p>
     *
     * @param int $days The number of days to add (may be negative).
     * @return LocalDate A new LocalDate shifted forward by the given count.
     */
    public function plusDays(int $days): LocalDate
    {
        $template = '%+d days';
        $modified = IsoDate::restore(canonical: $this->canonical)->modify(sprintf($template, $days));

        return new LocalDate(canonical: IsoDate::canonicalize(datetime: $modified));
    }

    /**
     * Returns the day of the week for this date.
     *
     * @return DayOfWeek The day of the week (Monday through Sunday).
     */
    public function dayOfWeek(): DayOfWeek
    {
        return DayOfWeek::from((int)IsoDate::restore(canonical: $this->canonical)->format('N'));
    }

    /**
     * Returns a copy of this date shifted backward by the given number of days.
     *
     * <p>A negative count shifts forward.</p>
     *
     * @param int $days The number of days to subtract (may be negative).
     * @return LocalDate A new LocalDate shifted backward by the given count.
     */
    public function minusDays(int $days): LocalDate
    {
        $template = '%+d days';
        $modified = IsoDate::restore(canonical: $this->canonical)->modify(sprintf($template, -$days));

        return new LocalDate(canonical: IsoDate::canonicalize(datetime: $modified));
    }

    /**
     * Returns a copy of this date shifted forward by the given number of years.
     *
     * <p>A negative count shifts backward. February 29 shifted onto a common year is clamped to
     * February 28, exactly as the month shift clamps in {@see LocalDate::plusMonths()}.</p>
     *
     * @param int $years The number of years to add (may be negative).
     * @return LocalDate A new LocalDate with the year shifted and the day clamped.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function plusYears(int $years): LocalDate
    {
        $lowestShift = (self::MIN_YEAR - $this->year());
        $highestShift = (self::MAX_YEAR - $this->year());

        if ($years < $lowestShift || $years > $highestShift) {
            throw InvalidLocalDate::becauseShiftIsOutOfRange();
        }

        $targetYear = ($this->year() + $years);
        $clampedDay = CalendarDay::clamp(year: $targetYear, month: $this->month(), day: $this->dayOfMonth());

        return LocalDate::of(year: $targetYear, month: $this->month(), day: $clampedDay);
    }

    /**
     * Returns the LocalDate as an ISO 8601 date string (YYYY-MM-DD).
     *
     * @return string The date in YYYY-MM-DD format (e.g. "2026-05-23").
     */
    public function toIso8601(): string
    {
        return $this->canonical;
    }

    /**
     * Returns the day of the month.
     *
     * @return int The day (1-31).
     */
    public function dayOfMonth(): int
    {
        return (int)IsoDate::restore(canonical: $this->canonical)->format('j');
    }

    /**
     * Returns a copy of this date shifted backward by the given number of years.
     *
     * <p>A negative count shifts forward. February 29 shifted onto a common year is clamped to
     * February 28, exactly as the month shift clamps in {@see LocalDate::plusMonths()}.</p>
     *
     * @param int $years The number of years to subtract (may be negative).
     * @return LocalDate A new LocalDate with the year shifted and the day clamped.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function minusYears(int $years): LocalDate
    {
        return $this->plusYears(years: -$years);
    }

    /**
     * Returns a copy of this date shifted forward by the given number of months.
     *
     * <p>A negative count shifts backward. The shift moves the year and month first, then clamps
     * the day to the last valid day of the target month. When the original day does not exist in a
     * shorter target month, the result lands on that month's final day.</p>
     *
     * <ul>
     * <li>2026-01-31 plus 1 month becomes 2026-02-28.</li>
     * <li>2028-01-31 plus 1 month becomes 2028-02-29.</li>
     * <li>2026-03-31 minus 1 month becomes 2026-02-28.</li>
     * </ul>
     *
     * <p>Because the day is clamped, the operation is not associative. Shifting 2026-01-31
     * forward by 1 month and then back by 1 month yields 2026-01-28, not the original day.</p>
     *
     * @param int $months The number of months to add (may be negative).
     * @return LocalDate A new LocalDate with the year and month shifted and the day clamped.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function plusMonths(int $months): LocalDate
    {
        $baseMonths = (($this->year() * 12) + ($this->month() - 1));
        $lowestShift = ((self::MIN_YEAR * 12) - $baseMonths);
        $highestShift = ((self::MAX_YEAR * 12) + 11 - $baseMonths);

        if ($months < $lowestShift || $months > $highestShift) {
            throw InvalidLocalDate::becauseShiftIsOutOfRange();
        }

        $totalMonths = ($baseMonths + $months);
        $targetYear = intdiv($totalMonths, 12);
        $targetMonth = (($totalMonths % 12) + 1);
        $clampedDay = CalendarDay::clamp(year: $targetYear, month: $targetMonth, day: $this->dayOfMonth());

        return LocalDate::of(year: $targetYear, month: $targetMonth, day: $clampedDay);
    }

    /**
     * Returns a copy of this date shifted backward by the given number of months.
     *
     * <p>A negative count shifts forward. The day is clamped to the last valid day of the target
     * month, exactly as in {@see LocalDate::plusMonths()}. Because of the clamp, the operation is
     * not associative.</p>
     *
     * @param int $months The number of months to subtract (may be negative).
     * @return LocalDate A new LocalDate with the year and month shifted and the day clamped.
     * @throws InvalidLocalDate If the resulting date falls outside the range 0001 to 9999.
     */
    public function minusMonths(int $months): LocalDate
    {
        return $this->plusMonths(months: -$months);
    }

    /**
     * Tells whether this date is after or equal to another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date is at or after the other.
     */
    public function isAfterOrEqual(LocalDate $other): bool
    {
        return IsoDate::restore(canonical: $this->canonical) >= IsoDate::restore(canonical: $other->canonical);
    }

    /**
     * Tells whether this date is before or equal to another.
     *
     * @param LocalDate $other The date to compare against.
     * @return bool True if this date is at or before the other.
     */
    public function isBeforeOrEqual(LocalDate $other): bool
    {
        return IsoDate::restore(canonical: $this->canonical) <= IsoDate::restore(canonical: $other->canonical);
    }
}
