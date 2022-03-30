<?php

namespace Codinglabs\FeatureFlags\Events;

use Illuminate\Database\Eloquent\Model;

class FeatureUpdatedEvent extends Event
{
    public Model $feature;

    public function __construct(Model $feature)
    {
        $this->feature = $feature;
    }
}
