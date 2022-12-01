<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->comment('Название очереди');
            $table->tinyInteger('status')->default(0)->comment('Состояние очереди: 1 - активная, 0 - неактивная, на сайте не будет показана)');
            $table->tinyInteger('is_cron')->default(0)->comment('Возможность по крону создавать слоты');
            $table->tinyInteger('slot_duration')->nullable()->default(30)->comment('Длительность слота, мин');
            $table->string('mon', 11)->nullable()->comment('время работы по понедельникам (08:00-17:00 или NULL, если приема нет)');
            $table->string('tue', 11)->nullable()->comment('время работы по вторникам (08:00-17:00 или NULL, если приема нет)');
            $table->string('wed', 11)->nullable()->comment('время работы по средам (08:00-17:00 или NULL, если приема нет)');
            $table->string('thu', 11)->nullable()->comment('время работы по четвергам (08:00-17:00 или NULL, если приема нет)');
            $table->string('fri', 11)->nullable()->comment('время работы по пятницам (08:00-17:00 или NULL, если приема нет)');
            $table->string('sat', 11)->nullable()->comment('время работы по субботам (08:00-17:00 или NULL, если приема нет)');
            $table->string('sun', 11)->nullable()->comment('время работы по воскресеньям (08:00-17:00 или NULL, если приема нет)');
            $table->string('break', 11)->nullable()->comment('время перерыва (12:00-13:00 или NULL, если перерыва нет)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queues');
    }
}
