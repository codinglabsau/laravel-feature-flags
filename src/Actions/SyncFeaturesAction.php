<?php

namespace Codinglabs\FeatureFlags\Actions;

use Illuminate\Database\Eloquent\Model;
use Codinglabs\FeatureFlags\Enums\FeatureState;

class SyncFeaturesAction
{
    public function __invoke(): void
    {
        $alwaysOn = app()->environment(config('feature-flags.always_on', []));

        $features = collect(config('feature-flags.features'))
            ->map(fn ($config, $name) => $this->normalise($name, $config, $alwaysOn));

        $featureModels = config('feature-flags.feature_model')::all();

        $featureModels->whereNotIn('name', $features->pluck('name'))
            ->each(fn (Model $feature) => $feature->delete());

        $features->each(function (array $feature) use ($featureModels) {
            $existing = $featureModels->firstWhere('name', $feature['name']);

            if (! $existing) {
                config('feature-flags.feature_model')::create($feature);

                return;
            }

            $updates = [];

            if ($existing->scope !== $feature['scope']) {
                $updates['scope'] = $feature['scope'];
            }

            if ($existing->description !== $feature['description']) {
                $updates['description'] = $feature['description'];
            }

            if ($updates) {
                $existing->update($updates);
            }
        });
    }

    private function normalise(string $name, mixed $config, bool $alwaysOn): array
    {
        if ($config instanceof FeatureState) {
            return [
                'name' => $name,
                'state' => $alwaysOn ? FeatureState::on() : $config,
                'scope' => null,
                'description' => null,
            ];
        }

        $scope = $config['scope'] ?? null;

        if ($scope instanceof \BackedEnum) {
            $scope = $scope->value;
        }

        return [
            'name' => $name,
            'state' => $alwaysOn ? FeatureState::on() : $config['state'],
            'scope' => $scope,
            'description' => $config['description'] ?? null,
        ];
    }
}
