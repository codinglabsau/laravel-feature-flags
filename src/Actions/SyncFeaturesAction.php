<?php

namespace Codinglabs\FeatureFlags\Actions;

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;

class SyncFeaturesAction
{
    public function __invoke(): void
    {
        $features = collect(config('feature-flags.features'))
            ->map(fn ($state, $name) => [
                'name' => $name,
                'state' => app()->environment(config('feature-flags.always_on', []))
                    ? FeatureState::on()
                    : $state
            ]);

        $featureModels = Feature::all();

        $featureModels->whereNotIn('name', $features->pluck('name'))
            ->each(fn (Feature $feature) => $feature->delete());

        $features->whereNotIn('name', $featureModels->pluck('name'))
            ->each(fn (array $feature) => Feature::create($feature));
    }
}
