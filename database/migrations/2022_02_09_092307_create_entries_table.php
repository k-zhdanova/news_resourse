<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->comment('ID пользователя из таблицы users');
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('service_id')->unsigned()->comment('ID сервиса из таблицы services');
            $table->foreign('service_id')->references('id')->on('services');
            $table->text('text')->nullable();
            $table->text('phone', 255)->nullable();
            $table->timestamps();
            $table->timestamp('started_at')->nullable()->comment('Дата початку опрацювання');
            $table->timestamp('finished_at')->nullable()->comment('Дата закінчення опрацювання');
            $table->timestamp('refused_at')->nullable()->comment('Дата відхилення');
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entries');
    }
}
