<?php

namespace Codinglabs\FeatureFlags\Commands;

use Illuminate\Console\Command;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

class TurnOnFeatureCommand extends Command
{
    protected $signature = 'feature:on {name : The feature name}';

    protected $description = 'Turn on a feature flag';

    public function handle(): int
    {
        $name = $this->argument('name');

        try {
            FeatureFlag::turnOn($name);
        } catch (MissingFeatureException) {
            $this->error("Feature '{$name}' not found.");

            return self::FAILURE;
        }

        $this->info("Feature '{$name}' has been turned on.");

        return self::SUCCESS;
    }
}
