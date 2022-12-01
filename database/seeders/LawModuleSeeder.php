<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LawModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->insert([
            'id'        => 22,
            'plural'    => 'laws_group',
            'singular'  => NULL,
            'name'      => 'Законодавство',
            'models'   => NULL,
            'icon'     => 'icon-File-HorizontalText',
            'sort'      => 10,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'id'        => 23,
            'parent_id' => 22,
            'plural'    => 'laws',
            'singular'  => 'law',
            'name'      => 'Закони',
            'models'   => NULL,
            'sort'      => 1,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'id'        => 24,
            'parent_id' => 22,
            'plural'    => 'law_categories',
            'singular'  => 'law_category',
            'name'      => 'Категорії законів',
            'models'   => NULL,
            'sort'      => 2,
            'default_order' => 'id|asc'
        ]);
    }
}
