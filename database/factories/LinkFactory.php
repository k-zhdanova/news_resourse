<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
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
            'link' => $this->faker->url(),
            'follow' => $this->faker->numberBetween(0, 1),
            'published_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
