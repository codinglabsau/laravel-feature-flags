<?php

use Codinglabs\FeatureFlags\FeatureFlags;
use Codinglabs\FeatureFlags\Models\Feature;

beforeEach(function() {
    cache()->flush();

    config([
        'features.cache_prefix' => 'testing',
        'features.cache_store' => 'array',
    ]);
});

it('feature is not enabled if it does not exist', function () {
    expect(FeatureFlags::enabled('some-feature'))->toBeFalse();
    expect(cache()->driver('array')->get('testing.some-feature'))->toBeNull();
});

it('feature is not enabled if state is off', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'off'
    ]);

    expect(FeatureFlags::enabled('some-feature'))->toBeFalse();
    expect(cache()->driver('array')->get('testing.some-feature'))->toBe('off');
});

it('feature is enabled if restricted and closure returns true', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'restricted'
    ]);

    FeatureFlags::restrictFeature('some-feature', function($feature) {
        expect($feature)->toBe('some-feature');

        return true;
    });

    expect(FeatureFlags::enabled('some-feature'))->toBeTrue();
    expect(cache()->driver('array')->get('testing.some-feature'))->toBe('restricted');
});

it('feature is not enabled if restricted and closure returns false', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => 'restricted'
    ]);

    FeatureFlags::restrictFeature('some-feature', function($feature) {
        expect($feature)->toBe('some-feature');

        return false;
    });

    expect(FeatureFlags::enabled('some-feature'))->toBeFalse();
    expect(cache()->driver('array')->get('testing.some-feature'))->toBe('restricted');
});