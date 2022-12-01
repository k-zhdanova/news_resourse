<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "code" => 3245,
            "name:en" => $this->faker->text(15),
            "name:uk" => $name = $this->faker->text(15),
            "meta_title:en" => $this->faker->text(15),
            "meta_title:uk" => $this->faker->text(15),
            "meta_description:en" => $this->faker->text(250),
            "meta_description:uk" => $this->faker->text(250),
            "is_online" => $this->faker->numberBetween(0, 1),
            "text:en" => $this->faker->text(50),
            "text:uk" => $this->faker->text(50),
            "is_free" => $this->faker->numberBetween(0, 1),
            "file1" => $this->faker->text(255),
            "file2" => $this->faker->text(255),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "place:en" => $this->faker->text(255),
            "place:uk" => $this->faker->text(255),
            "term:en" => $this->faker->text(255),
            "term:uk" => $this->faker->text(255),
            'published_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'uri' => Str::slug($name, '-'),
        ];
    }
}
