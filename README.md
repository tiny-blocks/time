# Time

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    * [Instant](#instant)
    * [Duration](#duration)
    * [Period](#period)
    * [DayOfWeek](#dayofweek)
    * [TimeOfDay](#timeofday)
    * [Timezone](#timezone)
    * [Timezones](#timezones)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div>

## Overview

Value Objects representing time in an immutable and strict way, focused on safe parsing, formatting, normalization and
temporal arithmetic.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/time
```

<div id='how-to-use'></div>

## How to use

The library provides immutable Value Objects for representing points in time, quantities of time and time intervals.
All instants are normalized to UTC internally.

### Instant

An `Instant` represents a single point on the timeline, always stored in UTC with microsecond precision.

#### Creating from the current moment

Captures the current moment with microsecond precision, normalized to UTC.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::now();

$instant->toIso8601();            # 2026-02-17T10:30:00+00:00
$instant->toUnixSeconds();        # 1771324200
$instant->toDateTimeImmutable();  # DateTimeImmutable (UTC, with microseconds)
```

#### Creating from a string

Parses a date-time string with an explicit UTC offset. The value is normalized to UTC regardless of the original offset.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17T13:30:00-03:00');

$instant->toIso8601();     # 2026-02-17T16:30:00+00:00
$instant->toUnixSeconds(); # 1771345800
```

#### Creating from a database timestamp

Parses a database date-time string as UTC, with or without microsecond precision (e.g. MySQL `DATETIME`
or `DATETIME(6)`).

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17 08:27:21.106011');

$instant->toIso8601();                                    # 2026-02-17T08:27:21+00:00
$instant->toDateTimeImmutable()->format('Y-m-d H:i:s.u'); # 2026-02-17 08:27:21.106011
```

Also supports timestamps without fractional seconds:

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17 08:27:21');

$instant->toIso8601(); # 2026-02-17T08:27:21+00:00
```

#### Creating from Unix seconds

Creates an `Instant` from a Unix timestamp in seconds.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromUnixSeconds(seconds: 0);

$instant->toIso8601();     # 1970-01-01T00:00:00+00:00
$instant->toUnixSeconds(); # 0
```

#### Adding and subtracting time

Returns a new `Instant` shifted forward or backward by a `Duration`.

```php
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Duration;

$instant = Instant::fromString(value: '2026-02-17T10:00:00+00:00');

$instant->plus(duration: Duration::fromMinutes(minutes: 30))->toIso8601();  # 2026-02-17T10:30:00+00:00
$instant->plus(duration: Duration::fromHours(hours: 2))->toIso8601();       # 2026-02-17T12:00:00+00:00
$instant->minus(duration: Duration::fromSeconds(seconds: 60))->toIso8601(); # 2026-02-17T09:59:00+00:00
```

#### Measuring distance between instants

Returns the absolute `Duration` between two `Instant` objects.

```php
use TinyBlocks\Time\Instant;

$start = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
$end = Instant::fromString(value: '2026-02-17T11:30:00+00:00');

$duration = $start->durationUntil(other: $end);

$duration->toSeconds(); # 5400
$duration->toMinutes(); # 90
$duration->toHours();   # 1
```

The result is always non-negative regardless of direction:

```php
$end->durationUntil(other: $start)->toSeconds(); # 5400
```

#### Comparing instants

Provides strict temporal ordering between two `Instant` instances.

```php
use TinyBlocks\Time\Instant;

$earlier = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
$later = Instant::fromString(value: '2026-02-17T10:30:00+00:00');

$earlier->isBefore(other: $later);         # true
$earlier->isAfter(other: $later);          # false
$earlier->isBeforeOrEqual(other: $later);  # true
$earlier->isAfterOrEqual(other: $later);   # false
$later->isAfter(other: $earlier);          # true
$later->isAfterOrEqual(other: $earlier);   # true
```

### Duration

A `Duration` represents an immutable, unsigned quantity of time measured in seconds. It has no reference point on the
timeline — it expresses only "how much" time.

#### Creating durations

```php
use TinyBlocks\Time\Duration;

$zero    = Duration::zero();
$seconds = Duration::fromSeconds(seconds: 90);
$minutes = Duration::fromMinutes(minutes: 30);
$hours   = Duration::fromHours(hours: 2);
$days    = Duration::fromDays(days: 7);
```

All factories reject negative values:

```php
Duration::fromMinutes(minutes: -5); # throws InvalidSeconds
```

#### Arithmetic

```php
use TinyBlocks\Time\Duration;

$thirtyMinutes = Duration::fromMinutes(minutes: 30);
$fifteenMinutes = Duration::fromMinutes(minutes: 15);

$thirtyMinutes->plus(other: $fifteenMinutes)->toSeconds();  # 2700 (45 minutes)
$thirtyMinutes->minus(other: $fifteenMinutes)->toSeconds(); # 900 (15 minutes)
```

Subtraction that would produce a negative result throws an exception:

```php
$fifteenMinutes->minus(other: $thirtyMinutes); # throws InvalidSeconds
```

#### Division

Returns the number of times one `Duration` fits wholly into another. The result is truncated toward zero:

```php
use TinyBlocks\Time\Duration;

$total = Duration::fromMinutes(minutes: 90);
$slot = Duration::fromMinutes(minutes: 30);

$total->divide(other: $slot); # 3
```

Division by a zero `Duration` throws an exception:

```php
$total->divide(other: Duration::zero()); # throws InvalidSeconds
```

#### Comparing durations

```php
use TinyBlocks\Time\Duration;

$short = Duration::fromMinutes(minutes: 15);
$long = Duration::fromHours(hours: 2);

$short->isLessThan(other: $long);    # true
$long->isGreaterThan(other: $short); # true
$short->isZero();                    # false
Duration::zero()->isZero();          # true
```

#### Converting to other units

Conversions truncate toward zero when the duration is not an exact multiple:

```php
use TinyBlocks\Time\Duration;

$duration = Duration::fromSeconds(seconds: 5400);

$duration->toSeconds(); # 5400
$duration->toMinutes(); # 90
$duration->toHours();   # 1
$duration->toDays();    # 0
```

### Period

A `Period` represents a half-open time interval `[from, to)` between two UTC instants. The start is inclusive and the
end is exclusive.

#### Creating from two instants

```php
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Period;

$period = Period::from(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
);

$period->from->toIso8601(); # 2026-02-17T10:00:00+00:00
$period->to->toIso8601();   # 2026-02-17T11:00:00+00:00
```

The start must be strictly before the end:

```php
Period::from(from: $later, to: $earlier); # throws InvalidPeriod
```

#### Creating from a start and duration

```php
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Period;

$period = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    duration: Duration::fromMinutes(minutes: 90)
);

$period->from->toIso8601(); # 2026-02-17T10:00:00+00:00
$period->to->toIso8601();   # 2026-02-17T11:30:00+00:00
```

#### Getting the duration

```php
$period->duration()->toSeconds(); # 5400
$period->duration()->toMinutes(); # 90
```

#### Checking if an instant is contained

The check is inclusive at the start and exclusive at the end:

```php
use TinyBlocks\Time\Instant;

$period->contains(instant: Instant::fromString(value: '2026-02-17T10:00:00+00:00')); # true (start, inclusive)
$period->contains(instant: Instant::fromString(value: '2026-02-17T10:30:00+00:00')); # true (middle)
$period->contains(instant: Instant::fromString(value: '2026-02-17T11:30:00+00:00')); # false (end, exclusive)
```

#### Detecting overlap

Two half-open intervals `[A, B)` and `[C, D)` overlap when `A < D` and `C < B`:

```php
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Period;

$periodA = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    duration: Duration::fromHours(hours: 1)
);
$periodB = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
    duration: Duration::fromHours(hours: 1)
);

$periodA->overlapsWith(other: $periodB); # true
$periodB->overlapsWith(other: $periodA); # true
```

Adjacent periods do not overlap:

```php
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Period;

$first = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    duration: Duration::fromHours(hours: 1)
);
$second = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T11:00:00+00:00'),
    duration: Duration::fromHours(hours: 1)
);

$first->overlapsWith(other: $second); # false
```

### DayOfWeek

A `DayOfWeek` represents a day of the week following ISO 8601, where Monday is 1 and Sunday is 7.

#### Deriving from an Instant

```php
use TinyBlocks\Time\DayOfWeek;
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
$day = DayOfWeek::fromInstant(instant: $instant);

$day;        # DayOfWeek::Tuesday
$day->value; # 2
```

#### Checking weekday or weekend

```php
use TinyBlocks\Time\DayOfWeek;

DayOfWeek::Monday->isWeekday();   # true
DayOfWeek::Monday->isWeekend();   # false
DayOfWeek::Saturday->isWeekday(); # false
DayOfWeek::Saturday->isWeekend(); # true
```

#### Calculating forward distance

Returns the number of days forward from one day to another, always in the range `[0, 6]`. The distance is measured
forward through the week:

```php
use TinyBlocks\Time\DayOfWeek;

DayOfWeek::Monday->distanceTo(other: DayOfWeek::Wednesday); # 2
DayOfWeek::Friday->distanceTo(other: DayOfWeek::Monday);    # 3 (forward through Sat, Sun, Mon)
DayOfWeek::Monday->distanceTo(other: DayOfWeek::Monday);    # 0
```

### TimeOfDay

A `TimeOfDay` represents a time of day (hour and minute) without date or timezone context. Values range from 00:00 to
23:59.

#### Creating from components

```php
use TinyBlocks\Time\TimeOfDay;

$time = TimeOfDay::from(hour: 8, minute: 30);

$time->hour;   # 8
$time->minute; # 30
```

#### Creating from a string

Parses a string in `HH:MM` or `HH:MM:SS` format. When seconds are present, they are discarded:

```php
use TinyBlocks\Time\TimeOfDay;

$time = TimeOfDay::fromString(value: '14:30');

$time->hour;   # 14
$time->minute; # 30
```

Also accepts the `HH:MM:SS` format commonly returned by databases:

```php
use TinyBlocks\Time\TimeOfDay;

$time = TimeOfDay::fromString(value: '08:30:00');

$time->hour;       # 8
$time->minute;     # 30
$time->toString(); # 08:30
```

#### Deriving from an Instant

Extracts the time of day from an `Instant` in UTC:

```php
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\TimeOfDay;

$instant = Instant::fromString(value: '2026-02-17T14:30:00+00:00');
$time = TimeOfDay::fromInstant(instant: $instant);

$time->hour;   # 14
$time->minute; # 30
```

#### Named constructors

```php
use TinyBlocks\Time\TimeOfDay;

$midnight = TimeOfDay::midnight(); # 00:00
$noon = TimeOfDay::noon();         # 12:00
```

#### Comparing times

```php
use TinyBlocks\Time\TimeOfDay;

$morning = TimeOfDay::from(hour: 8, minute: 0);
$afternoon = TimeOfDay::from(hour: 14, minute: 30);

$morning->isBefore(other: $afternoon);        # true
$morning->isAfter(other: $afternoon);         # false
$morning->isBeforeOrEqual(other: $afternoon); # true
$afternoon->isAfterOrEqual(other: $morning);  # true
```

#### Measuring distance between times

Returns the `Duration` between two times. The second time must be after the first:

```php
use TinyBlocks\Time\TimeOfDay;

$start = TimeOfDay::from(hour: 8, minute: 0);
$end = TimeOfDay::from(hour: 12, minute: 30);

$duration = $start->durationUntil(other: $end);

$duration->toMinutes(); # 270
```

#### Converting to other representations

```php
use TinyBlocks\Time\TimeOfDay;

$time = TimeOfDay::from(hour: 8, minute: 30);

$time->toMinutesSinceMidnight();  # 510
$time->toDuration()->toSeconds(); # 30600
$time->toString();                # 08:30
```

### Timezone

A `Timezone` is a Value Object representing a single valid [IANA timezone](https://www.iana.org) identifier.

#### Creating from an identifier

```php
use TinyBlocks\Time\Timezone;

$timezone = Timezone::from(identifier: 'America/Sao_Paulo');

$timezone->value;      # America/Sao_Paulo
$timezone->toString(); # America/Sao_Paulo
```

#### Creating a UTC timezone

```php
use TinyBlocks\Time\Timezone;

$timezone = Timezone::utc();

$timezone->value; # UTC
```

#### Converting to DateTimeZone

```php
use TinyBlocks\Time\Timezone;

$timezone = Timezone::from(identifier: 'Asia/Tokyo');
$dateTimeZone = $timezone->toDateTimeZone();

$dateTimeZone->getName(); # Asia/Tokyo
```

### Timezones

An immutable collection of `Timezone` objects.

#### Creating from objects

```php
use TinyBlocks\Time\Timezone;
use TinyBlocks\Time\Timezones;

$timezones = Timezones::from(
    Timezone::from(identifier: 'America/Sao_Paulo'),
    Timezone::from(identifier: 'America/New_York'),
    Timezone::from(identifier: 'Asia/Tokyo')
);

$timezones->count(); # 3
```

#### Creating from strings

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Europe/London');

$timezones->count();     # 3
$timezones->toStrings(); # ["UTC", "America/Sao_Paulo", "Europe/London"]
```

#### Getting all timezones

Returns all `Timezone` objects in the collection:

```php
$timezones->all(); # [Timezone("UTC"), Timezone("America/Sao_Paulo"), Timezone("Europe/London")]
```

#### Finding a timezone by identifier

Searches for a specific IANA identifier within the collection. Returns `null` if not found.

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo');

$timezones->findByIdentifier(iana: 'Asia/Tokyo');    # Timezone("Asia/Tokyo")
$timezones->findByIdentifier(iana: 'Europe/London'); # null
```

#### Finding a timezone by identifier with UTC fallback

Searches for a specific IANA identifier within the collection. Returns UTC if not found.

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Asia/Tokyo');

$timezones->findByIdentifierOrUtc(iana: 'Asia/Tokyo');    # Timezone("Asia/Tokyo")
$timezones->findByIdentifierOrUtc(iana: 'Europe/London'); # Timezone("UTC")
```

#### Checking if a timezone exists in the collection

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('America/Sao_Paulo', 'Asia/Tokyo');

$timezones->contains(iana: 'Asia/Tokyo');       # true
$timezones->contains(iana: 'America/New_York'); # false
```

#### Getting all identifiers as strings

Returns all timezone identifiers as plain strings:

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('UTC', 'America/Sao_Paulo', 'Europe/London');

$timezones->toStrings(); # ["UTC", "America/Sao_Paulo", "Europe/London"]
```

<div id='license'></div>

## License

Time is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
