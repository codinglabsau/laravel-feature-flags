<?php

use Codinglabs\FeatureFlags\FeatureFlags;
use Codinglabs\FeatureFlags\Models\Feature;

beforeEach(function() {
    cache()->flush();

    config([
        'feature-flags.cache_prefix' => 'testing',
        'feature-flags.cache_store' => 'array',
    ]);
});

it('generates the correct cache key', function () {
    expect(FeatureFlags::getFeatureKey('some-feature'))->toBe('testing.some-feature');
});

it('feature is not enabled if it does not exist', function () {
    expect(FeatureFlags::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->driver(config('feature-flags.cache_store'))->get('testing.some-feature'))->toBeNull();
});

it('feature is not enabled if state is off', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'off'
    ]);

    expect(FeatureFlags::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->driver(config('feature-flags.cache_store'))->get('testing.some-feature'))->toBe('off');
});

it('feature is enabled if restricted and closure returns true', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'restricted'
    ]);

    FeatureFlags::restrictFeatureWith('some-feature', function($feature) {
        expect($feature)->toBe('some-feature');

        return true;
    });

    expect(FeatureFlags::isEnabled('some-feature'))->toBeTrue();
    expect(cache()->driver(config('feature-flags.cache_store'))->get('testing.some-feature'))->toBe('restricted');
});

it('feature is not enabled if restricted and closure returns false', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'restricted'
    ]);

    FeatureFlags::restrictFeatureWith('some-feature', function($feature) {
        expect($feature)->toBe('some-feature');

        return false;
    });

    expect(FeatureFlags::isEnabled('some-feature'))->toBeFalse();
    expect(cache()->driver(config('feature-flags.cache_store'))->get('testing.some-feature'))->toBe('restricted');
});