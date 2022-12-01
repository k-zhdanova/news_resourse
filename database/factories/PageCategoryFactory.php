<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PageCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];
    }
}
