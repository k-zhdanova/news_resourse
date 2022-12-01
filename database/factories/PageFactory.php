<?php

namespace Database\Factories;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "name:en" => $this->faker->text(50),
            "name:uk" => $name = $this->faker->text(50),
            "text:en" => $this->faker->text(1000),
            "text:uk" => $this->faker->text(1000),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'uri' => Str::slug($name, '-'),
            "file1" => $this->faker->text(255),
            "file2" => $this->faker->text(255),
            "file3" => $this->faker->text(255),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "filename3:en" => $this->faker->text(255),
            "filename3:uk" => $this->faker->text(255),
        ];
    }
}
