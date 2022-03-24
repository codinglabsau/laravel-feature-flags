<?php

namespace Codinglabs\FeatureFlags;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Codinglabs\FeatureFlags\Commands\FeaturesCommand;

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
}
