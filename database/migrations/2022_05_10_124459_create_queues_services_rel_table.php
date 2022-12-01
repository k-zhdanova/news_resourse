<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueuesServicesRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues_services_rel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('queue_id')->unsigned()->comment('ID очереди из таблицы queues');
            $table->bigInteger('service_id')->unsigned()->comment('ID сервиса из таблицы services');
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queues_services_rel');
    }
}
