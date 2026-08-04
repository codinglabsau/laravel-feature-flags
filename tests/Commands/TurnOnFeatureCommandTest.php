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

it('turns on a feature', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::off(),
    ]);

    $this->artisan('feature:on', ['name' => 'search-v2'])
        ->expectsOutput("Feature 'search-v2' has been turned on.")
        ->assertSuccessful();

    expect(FeatureFlag::getState('search-v2'))->toBe(FeatureState::on());
});

it('fails when feature does not exist', function () {
    $this->artisan('feature:on', ['name' => 'nonexistent'])
        ->expectsOutput("Feature 'nonexistent' not found.")
        ->assertFailed();
});
