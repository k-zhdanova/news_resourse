<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\CabinetUserRoleSeeder;

class AddCabinetUserRoleToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new CabinetUserRoleSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        UserRole::where('id', 2)->delete();
        User::where('id', 2)->delete();
        Role::where('id', 3)->delete();
    }
}
