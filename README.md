# Dynamic feature flags for laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codinglabsau/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/codinglabsau/laravel-feature-flags)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/codinglabsau/laravel-feature-flags/run-tests?label=tests)](https://github.com/codinglabsau/laravel-feature-flags/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/codinglabsau/laravel-feature-flags/Check%20&%20fix%20styling?label=code%20style)](https://github.com/codinglabsau/laravel-feature-flags/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/codinglabsau/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/codinglabsau/laravel-feature-flags)

This package offers the ability to implement feature flags throughout your codebase allowing you to easily toggle parts of your application. Features are database driven which will allow you to easily configure them via a command or build a front end to manage their states. Here's an example of how they could be used:

```php
@feature('search-v2')
    // new search goes here
@else
    // legacy search here
@endfeature
```
And in your codebase:
```php
FeatureFlag::isEnabled('search-v2') // true
```
___
## Installation

You can install the package via composer:

```bash
composer require codinglabsau/laravel-feature-flags
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-feature-flags-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-feature-flags-config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure the cache store that will be used to cache the state of a
    | feature. You can also configure a prefix for all keys in the cache.
    */

    'cache_store' => env('FEATURES_CACHE_STORE'),
    'cache_prefix' => 'features',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you need to customise any models used then you can swap them out by
    | replacing the default models defined here. 
    */

    'feature_model' => \Codinglabs\FeatureFlags\Models\Feature::class,

];
```

## Usage

### Basic Setup

###Migrations
Make sure you have published the migrations as the `features` table is required:
```bash
php artisan vendor:publish --tag="laravel-feature-flags-migrations"
php artisan migrate
```

### Configuring Cache
Each features state will be cached on access which means it won't be calling the database every time a feature is being checked. You can configure the cache store by publishing the config:
```
php artisan vendor:publish --tag="laravel-feature-flags-config"
```
Then update your .env:
```php
FEATURES_CACHE_STORE=redis
```
Note that this package uses the `rememberForever()` method and that if you are using the `Memcached` driver, items that are stored "forever" may be removed when the cache reaches its size limit.

Create a new feature in the database and give it a default state:
```php
Feature::create([
    'name' => 'search-v2',
    'state' => Codinglabs\FeatureFlags\Enums\FeatureState::on()
]);
```
There are three states a feature can be in:
```php
use Codinglabs\FeatureFlags\Enums\FeatureState;

FeatureState::on()
FeatureState::off()
FeatureState::dynamic()
```
### Check If A Feature Is Enabled

#### Blade View
```php
@feature('search-v2')
    // new search goes here
@else
    // legacy search here
@endfeature
```
#### Code
```php
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

if (FeatureFlag::isEnabled('search-v2')) {
    // new feature code
} else {
    // old code
}
```

### Updating A Features State

To change a features state you can call the following methods:
```php
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

FeatureFlag::turnOn('search-v2');
FeatureFlag::turnOff('search-v2');
FeatureFlag::makeDynamic('search-v2');
```
Alternatively you can set the state directly by passing a feature state enum:
```php
FeatureFlag::updateFeatureState('search-v2', FeatureState::on())
```
It is recommended that you only update a features state using the above methods as it will take care of updating the cache.

### Dynamic Features

When a features state is in the dynamic state it will look for a dynamic handler to determine whether that feature is enabled or not. A dynamic handler can be defined in the `boot()` method of your `AppServiceProvider`:
```php
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

FeatureFlag::registerDynamicHandler('search-v2', function ($feature, $request) {
    return $request->user() && $request->user()->canAccessFeature($feature);
});
```
 Each handler is given the feature name and the current request as arguments and must return a bool.

#### Default Handler For Dynamic Features
You may also define a default dynamic handler which will be the catch-all dynamic handler for features that don't have an explicit handler defined for them:
```php
FeatureFlag::registerDefaultDynamicHandler(function ($feature, $request) {
    return $request->user() && $request->user()->hasRole('Tester');
});
```
When a feature is in the dynamic state it will look for an explicit handler for that feature first. If it can't find a handler and a default handler has been defined it will use that instead. If it can't find any handlers the feature will resolve to `off` by default.

### Events
#### Updated
After a feature has been updated an event will be dispatched:
```php
\Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent::class
```
This can be used to create a listener that could for example handle clearing any custom cache data created by dynamic handlers.

### Handle Missing Features
Features must exist in the database otherwise a `MissingFeatureException` will be thrown. This behaviour can be turned off by explicitly handling cases where a feature doesn't exist:
```php
FeatureFlag::handleMissingFeaturesWith(function ($feature) {
    // log or report this somewhere...
})
```
If a handler for missing features has been defined then an exception will **not** be thrown and the feature will resolve to `off`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Steve Thomas](https://github.com/codinglabsau)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
