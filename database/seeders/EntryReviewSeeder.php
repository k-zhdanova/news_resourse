<?php

namespace Database\Seeders;
use App\Models\EntryReview;
use Illuminate\Database\Seeder;

class EntryReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EntryReview::factory()
            ->count(5)
            ->create();
    }
}
