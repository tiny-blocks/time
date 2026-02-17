# Time

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    * [Instant](#instant)
    * [Timezone](#timezone)
    * [Timezones](#timezones)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div>

## Overview

Value Object representing time in an immutable and strict way, focused on safe parsing, formatting and normalization.
<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/time
```

<div id='how-to-use'></div>

## How to use

The library provides immutable Value Objects for representing points in time and IANA timezones. All instants are
normalized to UTC internally.

### Instant

An `Instant` represents a single point on the timeline, always stored in UTC with microsecond precision.

#### Creating from a string

Parses a date-time string with an explicit UTC offset. The value is normalized to UTC regardless of the original offset.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17T13:30:00-03:00');

$instant->toIso8601();           # 2026-02-17T16:30:00+00:00
$instant->toUnixSeconds();       # 1771345800
$instant->toDateTimeImmutable(); # DateTimeImmutable (UTC)
```

#### Creating from Unix seconds

Creates an `Instant` from a Unix timestamp in seconds.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromUnixSeconds(seconds: 0);

$instant->toIso8601();     # 1970-01-01T00:00:00+00:00
$instant->toUnixSeconds(); # 0
```

#### Creating from the current moment

Captures the current moment with microsecond precision, normalized to UTC.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::now();

$instant->toIso8601();           # 2026-02-17T10:30:00+00:00 (current UTC time)
$instant->toUnixSeconds();       # 1771324200 (current Unix timestamp)
$instant->toDateTimeImmutable(); # DateTimeImmutable (UTC, with microseconds)
```

#### Formatting as ISO 8601

The `toIso8601` method always returns the format `YYYY-MM-DDTHH:MM:SS+00:00`, without fractional seconds.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17T19:30:00+09:00');

$instant->toIso8601(); # 2026-02-17T10:30:00+00:00
```

#### Accessing the underlying DateTimeImmutable

Returns a `DateTimeImmutable` in UTC with full microsecond precision.

```php
use TinyBlocks\Time\Instant;

$instant = Instant::fromString(value: '2026-02-17T10:30:00+00:00');
$dateTime = $instant->toDateTimeImmutable();

$dateTime->getTimezone()->getName(); # UTC
$dateTime->format('Y-m-d\TH:i:s.u'); # 2026-02-17T10:30:00.000000
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

$timezones->findByIdentifierOrUtc(iana: 'Asia/Tokyo');     # Timezone("Asia/Tokyo")
$timezones->findByIdentifierOrUtc(iana: 'Europe/London');  # Timezone("UTC")
``` 

#### Checking if a timezone exists in the collection

```php
use TinyBlocks\Time\Timezones;

$timezones = Timezones::fromStrings('America/Sao_Paulo', 'Asia/Tokyo');

$timezones->contains(iana: 'Asia/Tokyo');       # true
$timezones->contains(iana: 'America/New_York'); # false
```

<div id='license'></div>

## License

Time is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
