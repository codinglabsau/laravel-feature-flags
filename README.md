# Dynamic feature flags for laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codinglabsau/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/codinglabsau/laravel-feature-flags)
[![Test](https://github.com/codinglabsau/laravel-feature-flags/actions/workflows/run-tests.yml/badge.svg)](https://github.com/codinglabsau/laravel-feature-flags/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/codinglabsau/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/codinglabsau/laravel-feature-flags)

This package offers the ability to implement feature flags in your application which can be easily toggled on or off. You can also set a feature to a dynamic state where you can define custom rules around whether that feature is enabled or not.
___
## Installation

### Install With Composer:

```bash
composer require codinglabsau/laravel-feature-flags
```

### Database Migrations

```bash
php artisan vendor:publish --tag="feature-flags-migrations"
php artisan migrate
```

### Publish Configuration:

```bash
php artisan vendor:publish --tag="feature-flags-config"
```


### Cache Store
update your `.env`:
```php
FEATURES_CACHE_STORE=file
```
Note that under the hood this package uses the `rememberForever()` method for caching and that if you are using the `Memcached` driver, items that are stored "forever" may be removed when the cache reaches its size limit.

### Use Your Own Model

To use your own model, update the config and replace the existing reference with your own model:

```php
// app/config/feature-flags.php

'feature_model' => \App\Models\Feature::class,
```

Make sure to also cast the state column to a feature state enum using the `FeatureStateCast`:

```php
// app/Models/Feature.php

use Codinglabs\FeatureFlags\Casts\FeatureStateCast;

protected $casts = [
    'state' => FeatureStateCast::class
];
```

## Usage

Create a new feature in the database and give it a default state:
```php
Feature::create([
    'name' => 'search-v2',
    'state' => Codinglabs\FeatureFlags\Enums\FeatureState::on()
]);
```

Its recommended that you seed the features to your database before a new deployment or as soon as possible after a deployment.

A feature can be in one of three states:
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

#### In Your Code
```php
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

if (FeatureFlag::isEnabled('search-v2')) {
    // new feature code
} else {
    // old code
}
```

#### Sharing features with UI (Inertiajs example)
```php
// config/app.php

'features' => [
    [
        'name' => 'search-v2',
        'state' => \Codinglabs\FeatureFlags\Enums\FeatureState::dynamic()
    ]
],
```

```php
// app/Middleware/HandleInertiaRequest.php

Inertia::share([
    'features' => function () {
        return collect(config('app.features'))
            ->filter(fn ($feature) => FeatureFlag::isEnabled($feature['name']))
            ->pluck('name');
    }
]);
```

```javascript
// app.js

Vue.mixin({
  methods: {
    hasFeature: function(feature) {
      return this.$page.features.includes(feature)
    }
  }
})
```
```html
<!-- SomeComponent.vue -->

<div v-if="hasFeature('search-v2')">Some cool new feature</div>
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
It is recommended that you only update a features state using the above methods as it will take care of updating the cache and dispatching the feature updated event:

```php
\Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent::class
```
An example use case of the feature updated event would be if you were caching the result of a dynamic handler and need to clear that cache when a feature is updated.

___
## Advanced Usage

### Dynamic Features

A dynamic handler can be defined in the `boot()` method of your `AppServiceProvider`:
```php
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

FeatureFlag::registerDynamicHandler('search-v2', function ($feature, $request) {
    return $request->user() && $request->user()->hasRole('Tester')
});
```
Dynamic handlers will only be called when a feature is in the `dynamic` state. This will allow you to define custom rules around whether that feature is enabled like in the example above where the user can only access the feature if they have a tester role. 

Each handler is provided with the features name and current request as arguments and must return a bool value.

### Default Handler For Dynamic Features

You may also define a default handler which will be the catch-all handler for features that don't have an explicit handler defined for them:

```php
FeatureFlag::registerDefaultDynamicHandler(function ($feature, $request) {
    return $request->user() && $request->user()->hasRole('Tester');
});
```

An explicit handler defined using `registerDynamicHandler()` will take precedence over the default handler. If neither a default nor explicit handler has been defined then the feature will resolve to `off` by default.

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

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jonathan Louw](https://github.com/JonathanLouw)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
