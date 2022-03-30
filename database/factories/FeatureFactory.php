<?php

namespace Codinglabs\FeatureFlags\Database\Factories;

use Codinglabs\FeatureFlags\Models\Feature;
use Codinglabs\FeatureFlags\Enums\FeatureState;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->slug(2),
            'state' => $this->faker->randomElement([
                FeatureState::on()->value,
                FeatureState::off()->value,
                FeatureState::dynamic()->value,
            ])
        ];
    }
}
