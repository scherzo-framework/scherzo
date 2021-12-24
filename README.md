# Scherzo web application framework for PHP

Yet another breaking rewrite for v0.8.

## Test
```console
$ php tools/phpunit.phar test --bootstrap ./vendor/autoload.php
```

## Installation
```console
composer require scherzo/scherzo
```

## Development on Linux
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
### Installation.
```console
$ # Install app.
$ composer install
$ # Install development tooling.
$ php tools/install.php
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
$ php tools/phpunit.phar
```

### Generate documentation
```console
$ # PHPDoc.
$ php tools/phpdoc.phar
$ # Code coverage.
$ php tools/phpunit.phar -c phpunit.coverage.xml --coverage-html docs/coverage --coverage-text
$ # Testdox.
$ php tools/phpunit.phar --testdox-html docs/test/index.html
```
