<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_rel', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tag_id')->unsigned()->comment('ID тега из таблицы tags');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->string('subject_type');
            $table->string('subject_id');
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
        Schema::dropIfExists('tag_rel');
    }
}
