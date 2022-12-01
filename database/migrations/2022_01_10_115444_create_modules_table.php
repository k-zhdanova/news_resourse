<?php

use Database\Seeders\ModuleSeeder;
use Database\Seeders\NewModuleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->unsigned()->comment('ID модуля из таблицы modules');
            $table->foreign('parent_id')->references('id')->on('modules')->constrained()->onDelete('cascade');
            $table->string('plural', 50)->nullable();
            $table->string('singular', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->text('models')->nullable()->comment('массив с путями к моделям сущностей');
            $table->smallInteger('is_publishable')->default(0)->comment('возможность публиковать записи или скрывать');
            $table->string('icon', 50)->nullable();
            $table->smallInteger('sort')->default(0);
            $table->string('default_order', 50)->nullable()->default('id|desc')->comment('сортировка в списке по-умолчанию');
        });

        $seeder = new ModuleSeeder();
        $seeder->run();

        $seeder = new NewModuleSeeder();
        $seeder->run();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('modules');
        Schema::enableForeignKeyConstraints();
    }
}
