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
 *
 * @param string $url Api endpoint to check
 * @param int $count Count of created models
 * @param array|string[] $sortingFields Sorting fields to check
 * @param array|string[] $auth Auth
 * @param string|null $envelope Results envelope (like 'results', 'items', etc.)
 *
 * @return void
 */
public function assertSortingWorks(string $url, int $count, array $sortingFields, array $auth, ?string $envelope): void
/**
 * Check that API returns list sorted by specified fields
 *  (order by multiple fields - check for combinations of passed fields).
 *
 * @param string $url Api endpoint to check
 * @param int $count Count of created models
 * @param array|string[] $sortingFields Sorting fields to check
 * @param array|string[] $auth Auth
 * @param string|null $envelope Results envelope (like 'results', 'items', etc.)
 *
 * @return void
 */
public function assertMultiSortingWorks(string $url, int $count, array $sortingFields, array $auth, ?string $envelope): void
```

#### Examples:
```php
    /** Sorting options test */
    public function testOrderBy()
    {
        $count = 15;
        $auth = Helpers::createAndAuthenticateUser();

        factory(Vendor::class, $count)->create();
        
        $envelope = 'results';
        
        $this->assertSortingWorks("/api/vendors", $count, VendorListRequest::SORTING_FIELDS, $auth, $envelope);
    }

    /** Multi sorting options test */
    public function testMultiOrderBy()
    {
        $count = 15;
        $auth = Helpers::createAndAuthenticateUser();

        factory(Vendor::class, $count)->create();
        
        $envelope = 'results';

        $this->assertMultiSortingWorks("api/vendors", $count, VendorListRequest::SORTING_FIELDS, $auth, $envelope);
    }
```
*NOTE: If the response does not contain an **envelope** (such as "results", "items", etc.), you do not need to send this parameter.*

#### Sorting by single field
##### To sort by one field in ascending order, only the field name is used. For example:
* api/vendors?order_by=name
* api/vendors?order_by=contacts.name
##### For sorting in descending order, the same is used, but with a minus. For example:
* api/vendors?order_by=-name
* api/vendors?order_by=-contacts.name

#### Sorting by several fields
##### To sort by multiple fields in ascending order, enumerate the field names. For example:
* api/vendors?order_by=id,name
* api/vendors?order_by=name,contacts.name
##### For sorting in descending order, the same is used, but with a minus. For example:
* api/vendors?order_by=-id,name
* api/vendors?order_by=-id,-contacts.name

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-testbed/issues)
* [Authors](https://github.com/Saritasa/php-laravel-testbed/contributors)
