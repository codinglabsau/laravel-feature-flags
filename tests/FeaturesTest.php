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

it('throws an exception if calling isEnabled for a feature that does not exist', function () {
    $this->expectException(MissingFeatureException::class);

    FeatureFlag::isEnabled('some-feature');
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

    expect(FeatureFlag::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBeNull();
});

it('resolves isEnabled to false when the features state is "off"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    expect(FeatureFlag::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::off()->value);
});

it('resolves isEnabled to true when the features state is "on"', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    expect(FeatureFlag::isEnabled('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});

it('resolves isEnabled to true when feature state is "restricted" and the closure returns true', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', function($feature) {
        return true;
    });

    expect(FeatureFlag::isEnabled('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isEnabled to false when feature state is "restricted" and the closure returns false', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', function($feature) {
        return false;
    });

    expect(FeatureFlag::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('uses the default restricted closure if no feature specific closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    FeatureFlag::registerDefaultDynamicHandler(function() {
        return true;
    });

    expect(FeatureFlag::isEnabled('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::dynamic()->value);
});

it('resolves isEnabled to false when feature state is "restricted" and no restricted closure has been defined', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic(),
    ]);

    expect(FeatureFlag::isEnabled('some-feature'))->toBeFalse();
});

it ('can update a features state', function () {
    Event::fake();

    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    cache()->store('array')->set('testing.some-feature', 'off');

    FeatureFlag::updateFeatureState('some-feature', FeatureState::on());

    Event::assertDispatched(\Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent::class);
    expect(FeatureFlag::isEnabled('some-feature'))->toBeTrue();
    expect(cache()->store('array')->get('testing.some-feature'))->toBe(FeatureState::on()->value);
});