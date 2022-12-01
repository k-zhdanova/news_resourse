<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        News::factory()
            ->count(10)
            ->create();
    }
}
