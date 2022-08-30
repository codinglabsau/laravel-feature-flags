<?php

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;

uses(InteractsWithViews::class);

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

it('does not reveal things when feature is off', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::off()
    ]);

    $view = $this->blade("@feature('some-feature') secret things @endfeature");

    $view->assertDontSee('secret things');
});

it('reveals things when feature is on ', function () {
    Feature::factory()->create([
        'name' => 'some-feature',
        'state' => FeatureState::on()
    ]);

    $view = $this->blade("@feature('some-feature') secret things @endfeature");

    $view->assertSee('secret things');
});
