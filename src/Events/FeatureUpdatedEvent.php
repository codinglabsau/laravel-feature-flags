<?php

namespace Codinglabs\FeatureFlags\Events;

class FeatureUpdatedEvent extends Event
{
    public $feature;

    public function __construct(string $feature)
    {
        $this->feature = config('feature-flags.feature_model')::where('name', $feature)->first();
    }
}