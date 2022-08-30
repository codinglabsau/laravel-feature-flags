<?php

namespace Codinglabs\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Codinglabs\FeatureFlags\Casts\FeatureStateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feature extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'state' => FeatureStateCast::class
    ];
}
