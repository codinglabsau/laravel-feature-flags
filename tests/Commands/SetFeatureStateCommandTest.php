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

it('sets a feature to a specific state', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::off(),
    ]);

    $this->artisan('feature:state', ['name' => 'search-v2', 'state' => 'dynamic'])
        ->expectsOutput("Feature 'search-v2' has been set to 'dynamic'.")
        ->assertSuccessful();

    expect(FeatureFlag::getState('search-v2'))->toBe(FeatureState::dynamic());
});

it('fails with an invalid state', function () {
    $this->artisan('feature:state', ['name' => 'search-v2', 'state' => 'invalid'])
        ->expectsOutput("Invalid state 'invalid'. Valid states: on, off, dynamic")
        ->assertFailed();
});

it('fails when feature does not exist', function () {
    $this->artisan('feature:state', ['name' => 'nonexistent', 'state' => 'on'])
        ->expectsOutput("Feature 'nonexistent' not found.")
        ->assertFailed();
});
