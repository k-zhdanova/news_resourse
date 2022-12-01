<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\RoleModule;
use Database\Seeders\LawRoleModuleSeeder;

class AddRolesToRolesModulesRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new LawRoleModuleSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        RoleModule::whereIn('id', [22, 23, 24])->delete();
    }
}
