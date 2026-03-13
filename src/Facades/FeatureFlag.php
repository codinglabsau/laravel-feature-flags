<?php

namespace Codinglabs\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use Codinglabs\FeatureFlags\FeatureFlags;
use Codinglabs\FeatureFlags\Enums\FeatureState;

/**
 * @see FeatureFlags
 *
 * @method static bool isOn(string $feature)
 * @method static bool isOff(string $feature)
 * @method static FeatureState getState(string $feature)
 * @method static void updateFeatureState(string $feature, FeatureState $state)
 * @method static void turnOn(string $feature)
 * @method static void turnOff(string $feature)
 * @method static void makeDynamic(string $feature)
 * @method static string getFeatureCacheKey(string $feature)
 * @method static void registerDynamicHandler(string $feature, \Closure $closure)
 * @method static void registerDefaultDynamicHandler(\Closure $closure)
 * @method static void handleMissingFeatureWith(\Closure $closure)
 * @method static ?string getScope(string $feature)
 * @method static ?string getDescription(string $feature)
 */
class FeatureFlag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'features';
    }
}
