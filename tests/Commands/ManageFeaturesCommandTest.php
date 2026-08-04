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

it('shows no features message when none exist', function () {
    $this->artisan('feature:manage')
        ->expectsOutput('No features found.')
        ->assertSuccessful();
});

it('displays features and allows exiting', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::on(),
    ]);

    $this->artisan('feature:manage')
        ->expectsTable(['Name', 'State'], [['search-v2', 'on']])
        ->expectsChoice('Select a feature to update', 'Exit', ['search-v2', 'Exit'])
        ->assertSuccessful();
});

it('allows toggling a feature state', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::on(),
    ]);

    $this->artisan('feature:manage')
        ->expectsTable(['Name', 'State'], [['search-v2', 'on']])
        ->expectsChoice('Select a feature to update', 'search-v2', ['search-v2', 'Exit'])
        ->expectsChoice("Set 'search-v2' to", 'off', ['on', 'off', 'dynamic'])
        ->expectsConfirmation("Set 'search-v2' to 'off'?", 'yes')
        ->expectsOutput("Feature 'search-v2' has been set to 'off'.")
        ->expectsTable(['Name', 'State'], [['search-v2', 'off']])
        ->expectsChoice('Select a feature to update', 'Exit', ['search-v2', 'Exit'])
        ->assertSuccessful();

    expect(FeatureFlag::getState('search-v2'))->toBe(FeatureState::off());
});

it('does not update when confirmation is declined', function () {
    Feature::factory()->create([
        'name' => 'search-v2',
        'state' => FeatureState::on(),
    ]);

    $this->artisan('feature:manage')
        ->expectsTable(['Name', 'State'], [['search-v2', 'on']])
        ->expectsChoice('Select a feature to update', 'search-v2', ['search-v2', 'Exit'])
        ->expectsChoice("Set 'search-v2' to", 'off', ['on', 'off', 'dynamic'])
        ->expectsConfirmation("Set 'search-v2' to 'off'?", 'no')
        ->expectsTable(['Name', 'State'], [['search-v2', 'on']])
        ->expectsChoice('Select a feature to update', 'Exit', ['search-v2', 'Exit'])
        ->assertSuccessful();

    expect(FeatureFlag::getState('search-v2'))->toBe(FeatureState::on());
});
