<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\RoleModule;
use Database\Seeders\EventRoleModuleSeeder;

class AddEventModuleToRolesModulesRelTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $seeder = new EventRoleModuleSeeder();
    $seeder->run();
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    RoleModule::whereIn('id', [25])->delete();
  }
}
