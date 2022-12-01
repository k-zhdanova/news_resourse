<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->insert([
            'plural'   => 'users_group',
            'singular' => NULL,
            'name'     => 'Користувачі',
            'models'   => NULL,
            'icon'     => 'icon-User', 
            'sort'     => 1,
            'default_order' => NULL
        ]);
        DB::table('modules')->insert([
            'parent_id' => 1,
            'plural'    => 'roles',
            'singular'  => 'role',
            'name'      => 'Ролі користувачів', 
            'models'   => '["App\\Models\\Role","App\\Models\\RoleModule"]',
            'sort'      => 1,
            'default_order' => 'name|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 1,
            'plural'    => 'users',
            'singular'  => 'user',
            'name'      => 'Користувачі',
            'models'   => '["App\\Models\\User","App\\Models\\UserRole"]',
            'sort'      => 2,
            'default_order' => NULL
        ]);
    }
}
