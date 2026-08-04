<?php

namespace Codinglabs\FeatureFlags;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Codinglabs\FeatureFlags\Commands\TurnOnFeatureCommand;
use Codinglabs\FeatureFlags\Commands\ManageFeaturesCommand;
use Codinglabs\FeatureFlags\Commands\TurnOffFeatureCommand;
use Codinglabs\FeatureFlags\Commands\SetFeatureStateCommand;

class FeatureFlagsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-feature-flags')
            ->hasConfigFile()
            ->hasMigration('create_features_table')
            ->hasCommands([
                ManageFeaturesCommand::class,
                TurnOnFeatureCommand::class,
                TurnOffFeatureCommand::class,
                SetFeatureStateCommand::class,
            ]);
    }

    public function packageRegistered()
    {
        $this->app->singleton('features', function () {
            return new FeatureFlags;
        });
    }

    public function packageBooted()
    {
        Blade::if('feature', function ($value) {
            return FeatureFlag::isOn($value);
        });
    }
}
