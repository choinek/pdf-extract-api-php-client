2024-12-22 - Almost ready but still WIP

# PDF Extract API PHP Client

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
