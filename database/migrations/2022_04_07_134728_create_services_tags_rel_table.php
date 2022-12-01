<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTagsRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_tags_rel', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tag_id')->unsigned()->comment('ID тега из таблицы tags');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->bigInteger('service_id')->unsigned()->comment('ID услуги из таблицы services');
            $table->foreign('service_id')->references('id')->on('services');
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
        Schema::dropIfExists('services_tags_rel');
    }
}
