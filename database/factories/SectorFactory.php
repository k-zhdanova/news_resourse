<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SectorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name:en" => $this->faker->text(15),
            "name:uk" => $name = $this->faker->text(15),
            "meta_title:en" => $this->faker->text(15),
            "meta_title:uk" => $this->faker->text(15),
            "meta_description:en" => $this->faker->text(250),
            "meta_description:uk" => $this->faker->text(250),
            "image" => $this->faker->text(15),
            'published_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'uri' => Str::slug($name, '-'),
        ];
    }
}

