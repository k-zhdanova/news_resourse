<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
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
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {

    Schema::dropIfExists('events');
  }
}
