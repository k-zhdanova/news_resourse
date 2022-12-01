<?php

namespace Database\Factories;

use App\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    protected $model = News::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "name:en" => $this->faker->text(15),
            "name:uk" => $name = $this->faker->text(15),
            "text:en" => $this->faker->text(100),
            "text:uk" => $this->faker->text(100),
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
