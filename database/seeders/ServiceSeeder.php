<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sector::factory()->create();
        Category::factory()->create();
        Institution::factory()->create();
        Service::factory()->create();
    }
}
