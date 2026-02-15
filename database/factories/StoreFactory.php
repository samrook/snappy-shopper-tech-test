<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Shop',
            // Generates coordinates roughly in the UK North West
            'location' => DB::raw("POINT({$this->faker->longitude(-3.0, -2.0)}, {$this->faker->latitude(53.0, 54.0)})"),
            'delivery_radius_km' => $this->faker->randomElement([5, 10, 15, 20]),
        ];
    }
}
