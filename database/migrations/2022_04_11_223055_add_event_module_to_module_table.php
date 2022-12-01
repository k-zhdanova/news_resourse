<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Module;
use Database\Seeders\EventModuleSeeder;

class AddEventModuleToModuleTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $seeder = new EventModuleSeeder();
    $seeder->run();
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Module::whereIn('id', [25])->delete();
  }
}
