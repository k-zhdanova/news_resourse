<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LawFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => 1,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'link' => $this->faker->url(),
            'published_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
