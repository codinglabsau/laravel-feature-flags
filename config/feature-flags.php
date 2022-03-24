<?php
// config for Codinglabs/FeatureFlags
return [

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure the cache driver that will be used to cache the state of a
    | feature. You can also configure a prefix for all keys in the cache.
    */

    'cache_store' => env('FEATURES_CACHE_STORE'),
    'cache_prefix' => 'features',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you need to customise any models used then you can swap them out by
    | replacing the default models defined here. Make sure you extend the
    | feature model if you do choose to create a custom model.
    */

    'feature_model' => \Codinglabs\FeatureFlags\Models\Feature::class,

];
