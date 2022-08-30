<?php

namespace Codinglabs\FeatureFlags\Actions;

use Illuminate\Database\Eloquent\Model;
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

        $featureModels = config('feature-flags.feature_model')::all();

        $featureModels->whereNotIn('name', $features->pluck('name'))
            ->each(fn (Model $feature) => $feature->delete());

        $features->whereNotIn('name', $featureModels->pluck('name'))
            ->each(fn (array $feature) => config('feature-flags.feature_model')::create($feature));
    }
}
