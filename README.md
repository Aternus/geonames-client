# GeoNames Client

A [GeoNames](http://www.geonames.org) API Client for PHP.

* [Quick Start](#quick-start)
* [Why?](#why)
* [Installation](#installation)
* [Other Useful Libraries](#other-useful-libraries)
* [License](#license)
* [Credits](#credits)


## Quick Start

An overview of available API parameters for each endpoint is 
[available here](http://www.geonames.org/export/ws-overview.html).
 
```php
<?php

use GeoNames\Client as GeoNamesClient;

$g = new GeoNamesClient('username');

// get a list of supported endpoints
$endpoints = $g->getSupportedEndpoints();

// get info for country
// note that I'm using the array destructor introduced in PHP 7.1
[$country] = $g->countryInfo([
    'country' => 'IL',
    'lang'    => 'ru', // display info in Russian
]);

// country name (in Russian)
$country_name = $country->countryName;

// spoken languages (ISO-639-1)
$country_languages = $country->languages;
```


## Why?

This library will allow you to get better insights into the world.

As a developer and a multi-lingual speaker I've always felt that localization was put
on last priority since it was so time consuming and error-prone.

Getting statistics for each country is a painful process that requires understanding the
different ISO standards, and even then you're still left to piece the puzzle together yourself.

Luckily, GeoNames have been collecting statistical data about the world for the past 
few decades and offers that data via their API.

The aim of this library is to provide a single source of truth for 
country [(ISO-3166)](https://en.wikipedia.org/wiki/ISO_3166),
language [(ISO-639-1)](https://en.wikipedia.org/wiki/ISO_639-1),
and other locale related statistical data, so that other developers can write better software 
which is up-to-date with the latest changes in the world.


## Installation

If you're using Composer to manage dependencies:
```
composer require aternus/geonames-client
```

Then, after running `composer update`, you can load the class using Composer's autoloading:

```php
require 'vendor/autoload.php';
```

Otherwise, you can simply require the file directly:

```php
require_once 'vendor/aternus/geonames-client/src/Client.php';
```

And in either case, I'd suggest using an alias.

```php
use GeoNames\Client as GeoNamesClient;
```


## Other Useful Libraries

Please make sure to implement some kind of a cache mechanism in order to save yourself
time, bandwidth and be respectful to GeoNames for providing all that data for free.

If you're making heavy use of the statistical data, you can subscribe to the their 
[Premium Data](http://www.geonames.org/products/premium-data.html) plan.


## License

Released under the MIT License - see `LICENSE.md` for details.


## Credits

David Jean Louis for the PEAR package which inspired this GeoNames API Client.
