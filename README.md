# Scherzo web application framework for PHP

Yet another breaking rewrite for v0.9.

[![Test](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml/badge.svg)](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml)

## Installation
```console
composer require scherzo/scherzo
```
## Development on Linux

Development branch
[![Test](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml/badge.svg?branch=develop)](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml)

### Installation.
```console
$ composer install
```

### Coding standards
```console
$ # Fix.
$ phpcbf
$ # Check.
$ phpcs
```

### Test
```console
$ phpunit
```

### Generate documentation
```console
$ phpdoc
$ phpunit -c phpunit.coverage.xml --coverage-html docs/coverage --coverage-text

```

## Development on Windows

Development branch
[![Test](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml/badge.svg?branch=develop)](https://github.com/scherzo-framework/scherzo/actions/workflows/ci.yaml)

### Installation.
```console
$ # Install app.
$ composer install
$ # Install development tooling.
$ php bin/install
```

### Coding standards
```console
$ # Fix.
$ php tools/phpcbf.phar
$ # Check.
$ php tools/phpcs.phar
```

### Test
```console
$ ./vendor/bin/phpunit

### Generate documentation
```console
$ # PHPDoc.
$ php tools/phpdoc.phar
$ # Code coverage.
$ ./vendor/bin/phpunit -c phpunit.coverage.xml --coverage-html docs/coverage --coverage-text
$ # Testdox.
$ ./vendor/bin/phpunit --testdox-html docs/test/index.html
```
