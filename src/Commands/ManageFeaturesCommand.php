<?php

namespace Codinglabs\FeatureFlags\Commands;

use Illuminate\Console\Command;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

class ManageFeaturesCommand extends Command
{
    protected $signature = 'feature:manage';

    protected $description = 'Interactively manage feature flags';

    public function handle(): int
    {
        $featureModel = config('feature-flags.feature_model');

        $features = $featureModel::all();

        if ($features->isEmpty()) {
            $this->info('No features found.');

            return self::SUCCESS;
        }

        while (true) {
            $this->displayFeaturesTable($features);

            $featureNames = $features->pluck('name')->toArray();
            $featureNames[] = 'Exit';

            $selected = $this->choice('Select a feature to update', $featureNames);

            if ($selected === 'Exit') {
                return self::SUCCESS;
            }

            $states = array_map(fn (FeatureState $state) => $state->value, FeatureState::cases());

            $newState = $this->choice(
                "Set '{$selected}' to",
                $states,
            );

            if ($this->confirm("Set '{$selected}' to '{$newState}'?")) {
                FeatureFlag::updateFeatureState($selected, FeatureState::from($newState));
                $this->info("Feature '{$selected}' has been set to '{$newState}'.");
            }

            $features = $featureModel::all();
        }
    }

    private function displayFeaturesTable($features): void
    {
        $rows = $features->map(fn ($feature) => [
            $feature->name,
            $feature->state->value,
        ])->toArray();

        $this->table(['Name', 'State'], $rows);
    }
}
