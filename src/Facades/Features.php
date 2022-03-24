<?php

namespace Codinglabs\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Codinglabs\FeatureFlags\FeatureFlags
 */
class Features extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-feature-flags';
    }
}
