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
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
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
            return FeatureFlag::isEnabled($value);
        });
    }
}
