<?php

namespace Codinglabs\FeatureFlags\Commands;

use Illuminate\Console\Command;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

class SetFeatureStateCommand extends Command
{
    protected $signature = 'feature:state {name : The feature name} {state : The state to set (on, off, dynamic)}';

    protected $description = 'Set a feature flag to a specific state';

    public function handle(): int
    {
        $name = $this->argument('name');
        $state = $this->argument('state');

        $validStates = array_map(fn (FeatureState $s) => $s->value, FeatureState::cases());

        if (! in_array($state, $validStates)) {
            $this->error("Invalid state '{$state}'. Valid states: " . implode(', ', $validStates));

            return self::FAILURE;
        }

        try {
            FeatureFlag::updateFeatureState($name, FeatureState::from($state));
        } catch (MissingFeatureException) {
            $this->error("Feature '{$name}' not found.");

            return self::FAILURE;
        }

        $this->info("Feature '{$name}' has been set to '{$state}'.");

        return self::SUCCESS;
    }
}
