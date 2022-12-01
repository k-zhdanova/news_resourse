<?php

namespace Database\Factories;

use App\Models\EntryReview;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryReviewFactory extends Factory
{
    protected $model = EntryReview::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id" => 1,
            "entry_id" => 1,
            "text" => $this->faker->text(20),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
