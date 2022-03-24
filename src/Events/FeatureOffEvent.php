<?php

namespace Codinglabs\FeatureFlags\Events;

class FeatureOffEvent extends Event
{
    public $feature;

    public function __construct(string $feature)
    {
        $this->feature = config('features.feature_model')::where('name', $feature)->first();
    }
}