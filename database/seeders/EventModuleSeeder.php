<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventModuleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('modules')->insert([
      'id'        => 25,
      'parent_id' => 4,
      'plural'    => 'events',
      'singular'  => 'event',
      'name'      => 'Записи у е-чергу',
      'models'    => NULL,
      'sort'      => 6,
      'default_order' => 'id|asc'
    ]);

    DB::table('modules')
      ->where('id', 19)
      ->update([
        'parent_id' => 4,
        'name'      => 'Замовлення послуги',
        'icon'     => NULL,
        'sort'      => 7
      ]);
  }
}
