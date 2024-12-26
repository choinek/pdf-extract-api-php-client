# PDF Extract API PHP Client

[![Code Coverage](https://raw.githubusercontent.com/choinek/pdf-extract-api-php-client/refs/heads/image-data/coverage.svg)](https://choinek.github.io/pdf-extract-api-php-client/)

> [!IMPORTANT]
> 2024-12-26 - Will be finished soon

## Description

PHP client for interacting with the [PDF Extract API](https://github.com/CatchTheTornado/pdf-extract-api).

## Installation

Install using Composer:

```bash
composer require choinek/pdf-extract-api-php-client
```

Ensure `ext-curl` is enabled in your PHP environment.

## Tests

### Internal Tests (Unit + Integration)

Run internal tests with:

```bash
composer test
```

### External Tests (Functional)

In order to run functional tests, you need to have the [PDF Extract API](https://github.com/CatchTheTornado/pdf-extract-api)
installed and running. Currently only http://localhost:8000 is supported for tests. (@todo)

Run functional tests with:
```bash
composer test-functional
```


## License

MIT

## Authors
- [Adrian Chojnicki](https://github.com/choinek)
