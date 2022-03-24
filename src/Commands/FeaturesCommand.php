<?php

namespace Codinglabs\FeatureFlags\Commands;

use Illuminate\Console\Command;

class FeaturesCommand extends Command
{
    public $signature = 'laravel-feature-flags';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
