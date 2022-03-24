<?php

namespace Codinglabs\FeatureFlags\Events;

use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use SerializesModels;
}
