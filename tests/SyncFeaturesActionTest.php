<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Tests\Fixtures\TestScope;
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

    (new SyncFeaturesAction())->__invoke();

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

    (new SyncFeaturesAction())->__invoke();

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

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseCount('features', 1);

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(),
    ]);

    $this->assertDatabaseMissing('features', [
        'name' => 'some-other-feature',
    ]);
});

it('overrides the state when the always on config is used and the environment matches', function () {
    app()->detectEnvironment(fn () => 'staging');

    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
            'some-other-feature' => FeatureState::off(),
            'some-dynamic-feature' => FeatureState::dynamic(),
        ],
        'feature-flags.always_on' => ['staging'],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseCount('features', 3);

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::on(),
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'some-other-feature',
        'state' => FeatureState::on(),
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'some-dynamic-feature',
        'state' => FeatureState::on(),
    ]);
});

it('syncs scope as null when using simple config format', function () {
    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::on(),
        'scope' => null,
    ]);
});

it('syncs scope when using rich config format', function () {
    config([
        'feature-flags.features' => [
            'some-feature' => [
                'state' => FeatureState::off(),
                'scope' => 'development',
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(),
        'scope' => 'development',
    ]);
});

it('supports mixed simple and rich config formats', function () {
    config([
        'feature-flags.features' => [
            'simple-feature' => FeatureState::on(),
            'rich-feature' => [
                'state' => FeatureState::off(),
                'scope' => 'release',
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseCount('features', 2);

    $this->assertDatabaseHas('features', [
        'name' => 'simple-feature',
        'scope' => null,
    ]);

    $this->assertDatabaseHas('features', [
        'name' => 'rich-feature',
        'scope' => 'release',
    ]);
});

it('updates scope on re-sync when config changes', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off(),
        'scope' => 'development',
    ]);

    config([
        'feature-flags.features' => [
            'some-feature' => [
                'state' => FeatureState::on(),
                'scope' => 'release',
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(), // state should NOT change
        'scope' => 'release', // scope SHOULD change
    ]);
});

it('resolves backed enum scope values', function () {
    $scope = TestScope::Development;

    config([
        'feature-flags.features' => [
            'some-feature' => [
                'state' => FeatureState::off(),
                'scope' => $scope,
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'scope' => 'development',
    ]);
});

it('syncs description when using rich config format', function () {
    config([
        'feature-flags.features' => [
            'some-feature' => [
                'state' => FeatureState::off(),
                'description' => 'New search powered by Meilisearch',
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'description' => 'New search powered by Meilisearch',
    ]);
});

it('syncs description as null when using simple config format', function () {
    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'description' => null,
    ]);
});

it('updates description on re-sync when config changes', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off(),
        'description' => 'Old description',
    ]);

    config([
        'feature-flags.features' => [
            'some-feature' => [
                'state' => FeatureState::on(),
                'description' => 'Updated description',
            ],
        ],
    ]);

    (new SyncFeaturesAction())->__invoke();

    $this->assertDatabaseHas('features', [
        'name' => 'some-feature',
        'state' => FeatureState::off(), // state should NOT change
        'description' => 'Updated description', // description SHOULD change
    ]);
});

it('does not override the state when the always on environment does not match', function () {
    app()->detectEnvironment(fn () => 'production');

    config([
        'feature-flags.features' => [
            'some-feature' => FeatureState::on(),
            'some-other-feature' => FeatureState::off(),
            'some-dynamic-feature' => FeatureState::dynamic(),
        ],
        'feature-flags.always_on' => ['local', 'staging'],
    ]);

    (new SyncFeaturesAction())->__invoke();

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
