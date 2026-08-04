<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

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

it('turns off a feature', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::on(),
    ]);

    $this->artisan('feature:off', ['name' => 'search-v2'])
        ->expectsOutput("Feature 'search-v2' has been turned off.")
        ->assertSuccessful();

    expect(FeatureFlag::getState('search-v2'))->toBe(FeatureState::off());
});

it('fails when feature does not exist', function () {
    $this->artisan('feature:off', ['name' => 'nonexistent'])
        ->expectsOutput("Feature 'nonexistent' not found.")
        ->assertFailed();
});
