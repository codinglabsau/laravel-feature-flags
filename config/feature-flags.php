<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Declare features that are managed by the app with the Feature
    | Flag package. The format is ['name' => FeatureState::on()].
    */

    'features' => [],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure the cache store that will be used to cache the state of a
    | feature. You can also configure a prefix for all keys in the cache.
    */

    'cache_store' => env('FEATURES_CACHE_STORE', config('cache.default')),

    'cache_prefix' => 'features',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you need to customise any models used then you can swap
    | them out by replacing the default models defined here.
    */

    'feature_model' => \Codinglabs\FeatureFlags\Models\Feature::class,

];
