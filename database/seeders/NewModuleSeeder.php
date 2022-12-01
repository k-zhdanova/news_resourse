<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->insert([
            'plural'   => 'sectors_group',
            'singular' => NULL,
            'name'     => 'Послуги',
            'models'   => NULL,
            'icon'     => 'icon-Hand-Touch', 
            'sort'     => 2,
            'default_order' => NULL
        ]);
        DB::table('modules')->insert([
            'parent_id' => 4,
            'plural'   => 'sectors',
            'singular' => 'sector',
            'name'     => 'Сфери послуг',
            'models'   => NULL,
            'sort'     => 1,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 4,
            'plural'    => 'categories',
            'singular'  => 'category',
            'name'      => 'Категорії послуг',
            'models'   => NULL,
            'sort'      => 2,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 4,
            'plural'    => 'institutions',
            'singular'  => 'institution',
            'name'      => 'Суб`єкти надання', 
            'models'   => NULL,
            'sort'      => 3,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 4,
            'plural'    => 'services',
            'singular'  => 'service',
            'name'      => 'Послуги', 
            'models'   => NULL,
            'sort'      => 4,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 4,
            'plural'    => 'tags',
            'singular'  => 'tag',
            'name'      => 'Теги', 
            'models'   => NULL,
            'sort'      => 5,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'pages_group',
            'singular'  => NULL,
            'name'      => 'Сторінки', 
            'models'   => NULL,
            'icon'     => 'icon-Sidebar-Window',
            'sort'      => 3,
            'default_order' => NULL
        ]);
        DB::table('modules')->insert([
            'parent_id' => 10,
            'plural'    => 'pages',
            'singular'  => 'page',
            'name'      => 'Сторінки', 
            'models'   => NULL,
            'sort'      => 1,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 10,
            'plural'    => 'page_categories',
            'singular'  => 'page_category',
            'name'      => 'Категорії сторінок', 
            'models'   => NULL,
            'sort'      => 2,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'links_group',
            'singular'  => NULL,
            'name'      => 'Посилання', 
            'models'   => NULL,
            'icon'     => 'icon-Link-2',
            'sort'      => 4,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 13,
            'plural'    => 'links',
            'singular'  => 'link',
            'name'      => 'Посилання', 
            'models'   => NULL,
            'sort'      => 1,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 13,
            'plural'    => 'link_categories',
            'singular'  => 'link_category',
            'name'      => 'Категорії посилань', 
            'models'   => NULL,
            'sort'      => 2,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'news_group',
            'singular'  => NULL,
            'name'      => 'Новини', 
            'models'   => NULL,
            'icon'     => 'icon-Newspaper',
            'sort'      => 5,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 16,
            'plural'    => 'news',
            'singular'  => 'news',
            'name'      => 'Новини', 
            'models'   => NULL,
            'sort'      => 1,
            'default_order' => 'updated_at|desc'
        ]);
        DB::table('modules')->insert([
            'parent_id' => 16,
            'plural'    => 'news_categories',
            'singular'  => 'news_category',
            'name'      => 'Категорії новин', 
            'models'   => NULL,
            'sort'      => 2,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'entries',
            'singular'  => 'entry',
            'name'      => 'Записи', 
            'models'   => NULL,
            'icon'     => 'icon-Pen',
            'sort'      => 6,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'reviews',
            'singular'  => 'review',
            'name'      => 'Відгуки', 
            'models'   => NULL,
            'icon'     => 'icon-Speach-Bubbles',
            'sort'      => 7,
            'default_order' => 'id|asc'
        ]);
        DB::table('modules')->insert([
            'plural'    => 'reports',
            'singular'  => 'report',
            'name'      => 'Звіти', 
            'models'   => NULL,
            'icon'     => 'icon-File-Upload',
            'sort'      => 8,
            'default_order' => 'id|asc'
        ]);
    }
}
