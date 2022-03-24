<?php

namespace Codinglabs\FeatureFlags;

use Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent;

class FeatureFlags
{
    public static array $restricted = [];

    public static function restrictFeatureWith(string $feature, callable $closure)
    {
        self::$restricted[$feature] = $closure;
    }

    public static function getFeatureKey(string $feature): string
    {
        return collect(
            array_filter([config('feature-flags.cache_prefix'), $feature], 'strlen')
        )->join('.');
    }

    public static function isEnabled(string $feature): bool
    {
        $featureKey = self::getFeatureKey($feature);

        $state = cache()->store(config('feature-flags.cache_store'))->rememberForever($featureKey, function () use ($feature) {
            if ($featureModel = config('feature-flags.feature_model')::where('name', $feature)->first()) {
                return $featureModel->state;
            }

            return null;
        });

        if ($state === null) {
            cache()->store(config('feature-flags.cache_store'))->forget($featureKey);

            return false;
        }

        switch ($state) {
            case 'on':
                return true;
            case 'off':
                return false;
            case 'restricted':
            {
                if (array_key_exists($feature, self::$restricted)) {
                    return self::$restricted[$feature]($feature, request()) === true;
                }

                return false;
            }
        }

        return false;
    }

    public static function updateFeatureState(string $feature, string $state)
    {
        $featureModel = config('feature-flags.feature_model')::where('name', $feature)->first();

        $featureModel->update(['state' => $state]);

        cache()->store(config('feature-flags.cache_store'))->forget(static::getFeatureKey($feature));

        event(new FeatureUpdatedEvent($feature));
    }
}
