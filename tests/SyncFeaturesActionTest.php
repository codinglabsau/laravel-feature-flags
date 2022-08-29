<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Actions\SyncFeaturesAction;

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

it('adds features that have no been synced', function () {
    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
            'some-other-feature' => FeatureState::off(),
            'some-dynamic-feature' => FeatureState::dynamic(),
        ],
    ]);

    (new SyncFeaturesAction)->__invoke();

    $this->assertDatabaseCount('features', 3);

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::on(),
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'some-other-feature',
        'state' => FeatureState::off(),
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'some-dynamic-feature',
        'state' => FeatureState::dynamic(),
    ]);
});

it('skips features that have already been synced even if the state has changed', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    Feature::factory()->create([
        'name' => 'some-other-feature',
        'state' => FeatureState::on()
    ]);

    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
            'some-other-feature' => FeatureState::on(),
        ],
    ]);

    (new SyncFeaturesAction)->__invoke();

    $this->assertDatabaseCount('features', 2);

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(),
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'some-other-feature',
        'state' => FeatureState::on(),
    ]);
});

it('removes features that have been removed', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    Feature::factory()->create([
        'name' => 'some-other-feature',
        'state' => FeatureState::on()
    ]);

    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::off(),
        ],
    ]);

    (new SyncFeaturesAction)->__invoke();

    $this->assertDatabaseCount('features', 1);

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(),
    ]);

    $this->assertDatabaseMissing('features', [
        'name' => 'some-other-feature',
    ]);
});
