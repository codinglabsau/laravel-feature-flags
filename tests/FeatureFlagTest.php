<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent;
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

it('throws an exception if casting to a feature state that does not exist', function () {
    $this->expectException(\InvalidArgumentException::class);

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'foo',
    ]);
});

it('throws an exception if calling isOn on a feature that does not exist', function () {
    $this->expectException(MissingFeatureException::class);

    FeatureFlag::isOn('some-feature');
    expect(cache()->store('array')->get('testing.some-feature'))->toBeNull();
});

it('throws an exception if calling isOff on a feature that does not exist', function () {
    $this->expectException(MissingFeatureException::class);

    FeatureFlag::isOff('some-feature');
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

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBeNull();
});

it('resolves isOn to false when the features state is "off"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::off()->value);
});

it('resolves isOn to true when the features state is "on"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue()
        ->and(FeatureFlag::isOff('some-feature'))->toBeFalse()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('resolves isOn to true when feature state is "dynamic" and the closure returns true', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', fn ($feature) => true);

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue()
        ->and(FeatureFlag::isOff('some-feature'))->toBeFalse()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isOn to false when feature state is "dynamic" and the closure returns false', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', fn ($feature) => false);

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('uses the default dynamic closure if no feature specific closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDefaultDynamicHandler(function () {
        return true;
    });

    expect(FeatureFlag::isOn('some-feature'))->toBeTrue()
        ->and(FeatureFlag::isOff('some-feature'))->toBeFalse()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isOn to false when feature state is "dynamic" and no dynamic closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue();
});

it('resolves the current state', function () {
    Feature::factory()->create([
        'name' => 'some-off-feature',
        'state' => FeatureState::off()
    ]);
    Feature::factory()->create([
        'name' => 'some-dynamic-feature',
        'state' => FeatureState::dynamic()
    ]);
    Feature::factory()->create([
        'name' => 'some-on-feature',
        'state' => FeatureState::on()
    ]);

    expect(FeatureFlag::getState('some-off-feature'))->toBe(FeatureState::off())
        ->and(FeatureFlag::getState('some-dynamic-feature'))->toBe(FeatureState::dynamic())
        ->and(FeatureFlag::getState('some-on-feature'))->toBe(FeatureState::on());
});

it('can turn on a feature', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    cache()->store('array')->set('testing.some-feature', 'off');

    FeatureFlag::turnOn('some-feature');

    Event::assertDispatched(FeatureUpdatedEvent::class);
    expect(FeatureFlag::isOn('some-feature'))->toBeTrue()
        ->and(FeatureFlag::isOff('some-feature'))->toBeFalse()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('can turn off a feature', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    cache()->store('array')->set('testing.some-feature', 'on');

    FeatureFlag::turnOff('some-feature');

    Event::assertDispatched(FeatureUpdatedEvent::class);
    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::off()->value);
});

it('can make a feature dynamic', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    cache()->store('array')->set('testing.some-feature', 'on');

    FeatureFlag::makeDynamic('some-feature');

    Event::assertDispatched(FeatureUpdatedEvent::class);
    expect(FeatureFlag::isOn('some-feature'))->toBeFalse()
        ->and(FeatureFlag::isOff('some-feature'))->toBeTrue()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('can update a features state', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    cache()->store('array')->set('testing.some-feature', 'off');

    FeatureFlag::updateFeatureState('some-feature', FeatureState::on());

    Event::assertDispatched(FeatureUpdatedEvent::class);
    expect(FeatureFlag::isOn('some-feature'))->toBeTrue()
        ->and(FeatureFlag::isOff('some-feature'))->toBeFalse()
        ->and(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('uses the default cache store when cache store has not been set', function () {
    config(['cache.default' => 'file']);

    config(['feature-flags.cache_store' => env('FEATURES_CACHE_STORE', config('cache.default'))]);

    expect(config('feature-flags.cache_store'))->toBe('file');
});
