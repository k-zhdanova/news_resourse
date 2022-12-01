<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plural'   => $this->faker->text(10),
            'singular' => NULL,
            'name'     => $this->faker->text(10),
            'models'   => NULL,
            'icon'     => 'icon-User',
            'sort'     => 1,
            'default_order' => NULL
        ];
    }
}
