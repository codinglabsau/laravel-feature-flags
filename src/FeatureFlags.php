<?php

namespace Codinglabs\FeatureFlags;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Cache\Repository;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Codinglabs\FeatureFlags\Events\FeatureUpdatedEvent;
use Codinglabs\FeatureFlags\Exceptions\MissingFeatureException;

class FeatureFlags
{
    const STATE_ON = 'on';
    const STATE_OFF = 'off';
    const STATE_DYNAMIC = 'dynamic';

    private static ?Closure $defaultDynamicHandler = null;
    private static ?Closure $handleMissingFeatureClosure = null;
    public static array $dynamicHandlers = [];

    private static function cache(): Repository
    {
        return Cache::store(config('feature-flags.cache_store'));
    }

    public static function getFeatureCacheKey(string $feature): string
    {
        $parts = [config('feature-flags.cache_prefix'), $feature];

        return implode('.', array_filter($parts, 'strlen'));
    }

    private static function getFeatureModel(string $feature): ?Model
    {
        if ($featureModel = config('feature-flags.feature_model')::firstWhere('name', $feature)) {
            return $featureModel;
        }

        if (is_callable(self::$handleMissingFeatureClosure)) {
            call_user_func(self::$handleMissingFeatureClosure, $feature);
        } else {
            throw new MissingFeatureException("Missing feature: {$feature}");
        }

        return null;
    }

    private static function getState(string $feature): FeatureState
    {
        $featureKey = self::getFeatureCacheKey($feature);

        $state = self::cache()->rememberForever($featureKey, function () use ($feature) {
            if ($featureModel = self::getFeatureModel($feature)) {
                return $featureModel->state->value;
            }

            return null;
        });

        if ($state === null) {
            self::cache()->forget($featureKey);

            return FeatureState::off();
        }

        return FeatureState::from($state);
    }

    public static function handleMissingFeatureWith(Closure $closure): void
    {
        self::$handleMissingFeatureClosure = $closure;
    }

    public static function isEnabled(string $feature): bool
    {
        $state = self::getState($feature);

        switch ($state) {
            case FeatureState::on():
                return true;
            case FeatureState::off():
                return false;
            case FeatureState::dynamic():
            {
                if (array_key_exists($feature, self::$dynamicHandlers)) {
                    return self::$dynamicHandlers[$feature]($feature, request()) === true;
                } elseif (is_callable(self::$defaultDynamicHandler)) {
                    return call_user_func(self::$defaultDynamicHandler, $feature, request()) === true;
                }

                return false;
            }
        }

        return false;
    }

    public static function reset(): void
    {
        self::$dynamicHandlers = [];
        self::$defaultDynamicHandler = null;
        self::$handleMissingFeatureClosure = null;
    }

    public static function makeDynamic(string $feature): void
    {
        self::updateFeatureState($feature, FeatureState::dynamic());
    }

    public static function registerDynamicHandler(string $feature, callable $closure): void
    {
        self::$dynamicHandlers[$feature] = $closure;
    }

    public static function registerDefaultDynamicHandler(Closure $closure): void
    {
        self::$defaultDynamicHandler = $closure;
    }

    public static function turnOn(string $feature): void
    {
        self::updateFeatureState($feature, FeatureState::on());
    }

    public static function turnOff(string $feature): void
    {
        self::updateFeatureState($feature, FeatureState::off());
    }

    public static function updateFeatureState(string $feature, FeatureState $state): void
    {
        if ($featureModel = self::getFeatureModel($feature)) {
            $featureModel->update(['state' => $state]);

            self::cache()->forget(static::getFeatureCacheKey($feature));

            event(new FeatureUpdatedEvent($featureModel));
        }
    }
}
