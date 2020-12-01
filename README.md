# Laravel Testbed

[![Build Status](https://github.com/Saritasa/php-laravel-testbed/workflows/build/badge.svg)](https://github.com/Saritasa/php-laravel-testbed/actions)
[![CodeCov](https://codecov.io/gh/Saritasa/php-laravel-testbed/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-testbed)
[![Release](https://img.shields.io/github/release/Saritasa/php-laravel-testbed.svg)](https://github.com/Saritasa/php-laravel-testbed/releases)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-testbed.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-testbed.svg)](https://packagist.org/packages/saritasa/laravel-testbed)

Helpers for feature tests, like API sorting, authentication, registration, etc.

## Usage

Install the ```saritasa/laravel-testbed``` package:

```bash
$ composer require saritasa/laravel-testbed
```

### ApiListSortingCheck Trait
Trait to check if sorting works correctly.

Using trait:
```php
use Saritasa\LaravelTestbed\Traits\ApiListSortingCheck;

/**
 * Vendor tests.
 */
class VendorTest extends TestCase
{
    use ApiListSortingCheck;
}
```

#### Available functions:
```php
/**
 * Check that API returns list sorted by specified field (order by single field - check for each of passed fields).
 **/
public function assertSortingWorks(string $url, int $count, array $sortingFields, array $auth): void
/**
 * Check that API returns list sorted by specified fields
 *  (order by multiple fields - check for combinations of passed fields).
 **/
public function assertMultiSortingWorks(string $url, int $count, array $sortingFields, array $auth): void
```

#### Examples:
```php
    /** Sorting options test */
    public function testOrderBy()
    {
        $count = 15;
        $auth = Helpers::createAndAuthenticateUser();

        factory(Vendor::class, $count)->create();
        
        $this->assertSortingWorks("/api/vendors", $count, VendorListRequest::SORTING_FIELDS, $auth);
    }

    /** Multi sorting options test */
    public function testMultiOrderBy()
    {
        $count = 15;
        $auth = Helpers::createAndAuthenticateUser();

        factory(Vendor::class, $count)->create();

        $this->assertMultiSortingWorks("api/vendors", $count, VendorListRequest::SORTING_FIELDS, $auth);
    }
```

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-testbed/issues)
* [Authors](https://github.com/Saritasa/php-laravel-testbed/contributors)
