# Aggregate

Laravel Eloquent allows you to query the count of a relationship using `withCount`. Aggregate extends Eloquent by adding `withSum`, `withAvg`, `withMin` and `withMax`.

## Installation

You can install the package via Composer:

```bash
composer require watson/aggregate
```

## Usage

The additional methods will be added by Laravel's autodiscovery feature. You can then use them the same way you already use `withCount`. [See the Laravel documentation for more on how this works](https://laravel.com/docs/5.7/eloquent-relationships#counting-related-models).

```php
Order::withSum('products')->get();
```

### Testing

``` bash
vendor/bin/phpunit
```
