<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        DB::table('roles')->insert([
            'name'       => 'Суперадмин',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        DB::table('roles')->insert([
            'name'       => 'Администратор',
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
