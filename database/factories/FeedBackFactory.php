<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedBackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'text' => $this->faker->text(20),
            'email' => $this->faker->email,
            'status' => $this->faker->numberBetween(1, 3),
            'date' => Carbon::now()->toDateTimeString(),
            'age' => $this->faker->numberBetween(18, 100),
            'sex' => $this->faker->randomElement(['male', 'female']),
            'service_id' => 1,
            'is_satisfied' => $this->faker->numberBetween(1, 5),
            'reception_friendly' => $this->faker->numberBetween(1, 5),
            'reception_competent' => $this->faker->numberBetween(1, 5),
            'center_friendly' => $this->faker->numberBetween(1, 5),
            'center_competent' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->numberBetween(1, 5),
            'impression' => $this->faker->numberBetween(1, 5),
        ];
    }
}
