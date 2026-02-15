<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Postcode>
 */
class PostcodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $latitude = $this->faker->latitude();
        $longitude = $this->faker->longitude();

        return [
            'postcode' => strtoupper($this->faker->bothify('??# #??')),
            // MariaDB POINT(longitude latitude)
            'location' => DB::raw("POINT($longitude $latitude)"), 
        ];
    }
}
