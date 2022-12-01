<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventIntervalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            'from' => $this->faker->time('H:i'),
            'till' => $this->faker->time('H:i'),
            'name' => $this->faker->text(15),
            'surname' => $this->faker->text(15),
            'middlename' => $this->faker->text(15),
            'text' => $this->faker->text(100),
            'phone' => $this->faker->e164PhoneNumber()
        ];
    }
}
