<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

beforeEach(function () {
    config([
        'feature-flags.cache_store' => 'array',
        'feature-flags.cache_prefix' => 'testing',
    ]);

    cache()->store('array')->clear();
});

afterEach(function () {
    FeatureFlag::reset();
});

it('throws an exception if calling isOn for a feature that does not exist', function () {
    $this->expectException(MissingFeatureException::class);

    FeatureFlag::isOn('some-feature');
    expect(cache()->store('array')->get('testing.some-feature'))->toBeNull();
});

it('generates the correct cache key', function () {
    config(['feature-flags.cache_prefix' => 'some-prefix']);

    expect(FeatureFlag::getFeatureCacheKey('some-feature'))->toBe('some-prefix.some-feature');
});

it('handles a missing feature exception when a global handler has been defined', function () {
    FeatureFlag::handleMissingFeatureWith(function ($feature) {
        // handling...
    });

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBeNull();
});

it('resolves isOn to false when the features state is "off"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::off()->value);
});

it('resolves isOn to true when the features state is "on"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('resolves isOn to true when feature state is "dynamic" and the closure returns true', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', function ($feature) {
        return true;
    });

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isOn to false when feature state is "dynamic" and the closure returns false', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', function ($feature) {
        return false;
    });

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('uses the default dynamic closure if no feature specific closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDefaultDynamicHandler(function () {
        return true;
    });

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isOn to false when feature state is "dynamic" and no dynamic closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse();
});

it('can update a features state', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    cache()->store('array')->set('testing.some-feature', 'off');

    FeatureFlag::updateFeatureState('some-feature', FeatureState::on());

    Event::assertDispatched(\Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent::class);
    expect(FeatureFlag::isOn('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('uses the default cache store when cache store has not been set', function () {
    config(['cache.default' => 'file']);

    config(['feature-flags.cache_store' => env('FEATURES_CACHE_STORE', config('cache.default'))]);

    expect(config('feature-flags.cache_store'))->toBe('file');
});
