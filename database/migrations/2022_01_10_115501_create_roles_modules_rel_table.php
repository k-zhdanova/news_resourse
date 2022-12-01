<?php

use Database\Seeders\RoleModuleSeeder;
use Database\Seeders\NewRoleModuleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesModulesRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles_modules_rel', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->unsigned()->nullable()->comment('ID роли пользователя из таблицы roles');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->bigInteger('module_id')->unsigned()->nullable()->comment('ID модуля из таблицы modules');
            $table->foreign('module_id')->references('id')->on('modules');
            $table->string('scopes', 255)->nullable()->comment('JSON объект с правами');
            $table->timestamps();
        });

        $seeder = new RoleModuleSeeder();
        $seeder->run();

        $seeder = new NewRoleModuleSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_modules_rel');
    }
}
