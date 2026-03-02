# Time

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    * [Instant](#instant)
    * [Duration](#duration)
    * [Period](#period)
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

$instant->plus(duration: Duration::ofMinutes(minutes: 30))->toIso8601();  # 2026-02-17T10:30:00+00:00
$instant->plus(duration: Duration::ofHours(hours: 2))->toIso8601();       # 2026-02-17T12:00:00+00:00
$instant->minus(duration: Duration::ofSeconds(seconds: 60))->toIso8601(); # 2026-02-17T09:59:00+00:00
```

#### Measuring distance between instants

Returns the absolute `Duration` between two `Instant` objects.

```php
use TinyBlocks\Time\Instant;

$start = Instant::fromString(value: '2026-02-17T10:00:00+00:00');
$end = Instant::fromString(value: '2026-02-17T11:30:00+00:00');

$duration = $start->durationUntil(other: $end);

$duration->seconds;     # 5400
$duration->toMinutes(); # 90
$duration->toHours();   # 1
```

The result is always non-negative regardless of direction:

```php
$end->durationUntil(other: $start)->seconds; # 5400
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
$seconds = Duration::ofSeconds(seconds: 90);
$minutes = Duration::ofMinutes(minutes: 30);
$hours   = Duration::ofHours(hours: 2);
$days    = Duration::ofDays(days: 7);
```

All factories reject negative values:

```php
Duration::ofMinutes(minutes: -5); # throws InvalidDuration
```

#### Arithmetic

```php
use TinyBlocks\Time\Duration;

$a = Duration::ofMinutes(minutes: 30);
$b = Duration::ofMinutes(minutes: 15);

$a->plus(other: $b)->seconds;  # 2700 (45 minutes)
$a->minus(other: $b)->seconds; # 900 (15 minutes)
```

Subtraction that would produce a negative result throws an exception:

```php
$b->minus(other: $a); # throws InvalidDuration
```

#### Comparing durations

```php
use TinyBlocks\Time\Duration;

$short = Duration::ofMinutes(minutes: 15);
$long = Duration::ofHours(hours: 2);

$short->isLessThan(other: $long);    # true
$long->isGreaterThan(other: $short); # true
$short->isZero();                    # false
Duration::zero()->isZero();          # true
```

#### Converting to other units

Conversions truncate toward zero when the duration is not an exact multiple:

```php
use TinyBlocks\Time\Duration;

$duration = Duration::ofSeconds(seconds: 5400);

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

$period = Period::of(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    to: Instant::fromString(value: '2026-02-17T11:00:00+00:00')
);

$period->from->toIso8601(); # 2026-02-17T10:00:00+00:00
$period->to->toIso8601();   # 2026-02-17T11:00:00+00:00
```

The start must be strictly before the end:

```php
Period::of(from: $later, to: $earlier); # throws InvalidPeriod
```

#### Creating from a start and duration

```php
use TinyBlocks\Time\Duration;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\Period;

$period = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:00:00+00:00'),
    duration: Duration::ofMinutes(minutes: 90)
);

$period->from->toIso8601(); # 2026-02-17T10:00:00+00:00
$period->to->toIso8601();   # 2026-02-17T11:30:00+00:00
```

#### Getting the duration

```php
$period->duration()->seconds;    # 5400
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
    duration: Duration::ofHours(hours: 1)
);
$periodB = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T10:30:00+00:00'),
    duration: Duration::ofHours(hours: 1)
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
    duration: Duration::ofHours(hours: 1)
);
$second = Period::startingAt(
    from: Instant::fromString(value: '2026-02-17T11:00:00+00:00'),
    duration: Duration::ofHours(hours: 1)
);

$first->overlapsWith(other: $second); # false
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
