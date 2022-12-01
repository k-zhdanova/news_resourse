<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('event_intervals');
        Schema::dropIfExists('events');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('service_id')->nullable()->unsigned()->comment('ID услуги из таблицы services');
            $table->foreign('service_id')->references('id')->on('services');
            $table->smallInteger('duration')->nullable();
            $table->timestamp('started_at')->nullable()->comment('Дата ближайшего интервала');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_intervals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->nullable()->comment('ID пользователя из таблицы users');
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('event_id')->unsigned()->comment('ID события из таблицы events');
            $table->foreign('event_id')->references('id')->on('events');
            $table->date('date')->nullable()->comment('Дата события');
            $table->time('from', 0)->nullable()->comment('Время начала события');
            $table->time('till', 0)->nullable()->comment('Время окончания события');
            $table->string('name', 255)->nullable();
            $table->string('surname', 255)->nullable();
            $table->string('middlename', 255)->nullable();
            $table->text('text')->nullable();
            $table->text('phone', 255)->nullable();
            $table->timestamp('accepted_at')->nullable()->comment('Дата погодження прийому');
            $table->bigInteger('accepted_by')->nullable()->unsigned()->comment('ID пользователя из таблицы users');
            $table->foreign('accepted_by')->references('id')->on('users');
            $table->timestamp('closed_at')->nullable()->comment('Дата закриття прийому');
            $table->bigInteger('closed_by')->nullable()->unsigned()->comment('ID пользователя из таблицы users');
            $table->foreign('closed_by')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }
}
