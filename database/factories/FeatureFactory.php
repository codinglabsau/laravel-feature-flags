<?php

namespace Codinglabs\FeatureFlags\Database\Factories;

use Codinglabs\FeatureFlags\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->slug(2),
            'state' => $this->faker->randomElement(['on', 'off', 'restricted'])
        ];
    }
}
