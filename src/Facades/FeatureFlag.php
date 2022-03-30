<?php

namespace Codinglabs\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use Codinglabs\FeatureFlags\Enums\FeatureState;

/**
 * @see \Codinglabs\FeatureFlags\FeatureFlags
 *
 * @method static bool isEnabled(string $feature)
 * @method static void updateFeatureState(string $feature, FeatureState $state)
 * @method static void turnOn(string $feature)
 * @method static void turnOff(string $feature)
 * @method static void makeDynamic(string $feature)
 * @method static string getFeatureCacheKey(string $feature)
 * @method static void registerDynamicHandler(string $feature, \Closure $closure)
 * @method static void registerDefaultDynamicHandler(\Closure $closure)
 * @method static void handleMissingFeatureWith(\Closure $closure)
 */
class FeatureFlag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'features';
    }
}
