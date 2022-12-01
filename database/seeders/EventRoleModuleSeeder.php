<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventRoleModuleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $now = Carbon::now()->toDateTimeString();

    DB::table('roles_modules_rel')->insert([
      'role_id' => 1,
      'module_id' => 25,
      'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}',
      'created_at' => $now,
      'updated_at' => $now
    ]);
  }
}
