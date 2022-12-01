<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QueueFactory extends Factory
{
    public const DEFAULT_WORK_TIME = '08:00-17:00';
    public const DEFAULT_BREAK_TIME = '12:00-13:00';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(15),
            'status' => $this->faker->numberBetween(0, 1),
            'is_cron' => $this->faker->numberBetween(0, 1),
            'slot_duration' => $this->faker->numberBetween(30, 60),
            'mon' => self::DEFAULT_WORK_TIME,
            'tue' => self::DEFAULT_WORK_TIME,
            'wed' => self::DEFAULT_WORK_TIME,
            'thu' => self::DEFAULT_WORK_TIME,
            'fri' => self::DEFAULT_WORK_TIME,
            'sat' => self::DEFAULT_WORK_TIME,
            'sun' => self::DEFAULT_WORK_TIME,
            'break' => self::DEFAULT_BREAK_TIME,
        ];
    }
}
