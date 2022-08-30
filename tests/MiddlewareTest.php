<?php

use Illuminate\Support\Facades\Route;
use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Middleware\VerifyFeatureIsOn;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

beforeEach(function () {
    config([
        'feature-flags.cache_store' => 'array',
        'feature-flags.cache_prefix' => 'testing',
    ]);

    Route::get('test-middleware', function () {
        return 'ok';
    })->middleware(VerifyFeatureIsOn::class . ':some-feature');

    cache()->store('array')->clear();
});

afterEach(function () {
    FeatureFlag::reset();
});

it('returns a 500 status when a feature does not exist', function () {
    $this->withoutExceptionHandling();

    $this->expectException(MissingFeatureException::class);

    $this->get('test-middleware')
        ->assertStatus(500);
});

it('returns a 404 status when a feature is off', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    $this->get('test-middleware')
        ->assertStatus(404);
});

it('returns a 404 status when a feature is dynamic', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic()
    ]);

    $this->get('test-middleware')
        ->assertStatus(404);
});

it('returns an ok status when a feature is dynamic and enabled', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::dynamic()
    ]);

    FeatureFlag::registerDynamicHandler('some-feature', fn ($feature) => true);

    $this->get('test-middleware')
        ->assertOk();
});

it('returns an ok status when a feature is on', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    $this->get('test-middleware')
        ->assertOk();
});
