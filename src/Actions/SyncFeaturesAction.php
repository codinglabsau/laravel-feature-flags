<?php

namespace Codinglabs\FeatureFlags\Actions;

use Codinglabs\FeatureFlags\Models\Feature;

class SyncFeaturesAction
{
    public function __invoke(): void
    {
        $features = collect(config('feature-flags.features'))
            ->map(fn ($state, $name) => [
                'name' => $name,
                'state' => $state
            ]);

        $featureModels = Feature::all();

        $featureModels->whereNotIn('name', $features->pluck('name'))
            ->each(fn (Feature $feature) => $feature->delete());

        $features->whereNotIn('name', $featureModels->pluck('name'))
            ->each(fn (array $feature) => Feature::create($feature));
    }
}
