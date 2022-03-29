<?php

namespace Codinglabs\FeatureFlags\Models;

use Codinglabs\FeatureFlags\Casts\FeatureStateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feature extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'state' => FeatureStateCast::class
    ];
}