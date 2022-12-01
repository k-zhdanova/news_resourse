<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntryfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entry_id')->unsigned()->comment('ID заявки из таблицы entries');
            $table->foreign('entry_id')->references('id')->on('entries');
            $table->string('filename');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entry_files');
    }
}
