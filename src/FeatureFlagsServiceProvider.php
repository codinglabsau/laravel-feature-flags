<?php

namespace Codinglabs\FeatureFlags;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FeatureFlagsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-feature-flags')
            ->hasConfigFile()
            ->hasMigration('create_features_table');
    }

    public function packageRegistered()
    {
        $this->app->singleton('features', function () {
            return new FeatureFlags();
        });
    }

    public function packageBooted()
    {
        Blade::if('feature', function ($value) {
            return FeatureFlag::isOn($value);
        });
    }
}
