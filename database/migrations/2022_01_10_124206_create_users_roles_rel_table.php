<?php

use Database\Seeders\UserRoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersRolesRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_roles_rel', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->comment('ID пользователя из таблицы users');
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('role_id')->unsigned()->nullable()->comment('ID роли из таблицы roles');
            $table->foreign('role_id')->references('id')->on('roles');
        });

        $seeder = new UserRoleSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_roles_rel');
    }
}
