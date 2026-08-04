<?php

namespace Codinglabs\FeatureFlags\Commands;

use Illuminate\Console\Command;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

class TurnOffFeatureCommand extends Command
{
    protected $signature = 'feature:off {name : The feature name}';

    protected $description = 'Turn off a feature flag';

    public function handle(): int
    {
        $name = $this->argument('name');

        try {
            FeatureFlag::turnOff($name);
        } catch (MissingFeatureException) {
            $this->error("Feature '{$name}' not found.");

            return self::FAILURE;
        }

        $this->info("Feature '{$name}' has been turned off.");

        return self::SUCCESS;
    }
}
