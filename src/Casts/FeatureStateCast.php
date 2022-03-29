<?php

namespace Codinglabs\FeatureFlags\Casts;

use InvalidArgumentException;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class FeatureStateCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes)
    {
        return FeatureState::from($attributes['state']);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof FeatureState) {
            throw new InvalidArgumentException('The given value is not an instance of FeatureState.');
        }

        return [
            'state' => $value->value
        ];
    }
}