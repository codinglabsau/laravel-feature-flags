<?php

use Codinglabs\FeatureFlags\Models\Feature;

return [

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Declare features that are managed by the app with the Feature
    | Flag package. The format is ['name' => FeatureState::on()].
    |
    | You can also use a rich format to assign a scope and description:
    | 'name' => [
    |     'state' => FeatureState::off(),
    |     'scope' => 'development',
    |     'description' => 'New search powered by Meilisearch',
    | ]
    |
    | Scope is a free-form string for categorising flags. Apps can
    | use it to filter which flags are shown in admin UIs, e.g.
    | Feature::scope('release')->get().
    |
    | Description is a human-readable explanation of what the feature
    | does, useful for admin UIs where non-developers manage flags.
    |
    | Both scope and description sync from config on every deploy,
    | unlike state which is only set on creation.
    */

    'features' => [],

    /*
    |--------------------------------------------------------------------------
    | Always On
    |--------------------------------------------------------------------------
    |
    | Declare the environments where features will be synced to the on
    | state. This is useful if features should always be on locally.
    | Note this only impacts the behaviour of the sync action.
    */

    'always_on' => [],

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

    'feature_model' => Feature::class,

];
