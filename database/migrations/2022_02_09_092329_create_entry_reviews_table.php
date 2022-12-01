<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntryReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()
                ->comment('ID пользователя из таблицы users оставившего коментарий');
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('entry_id')->unsigned()->comment('ID заявки из таблицы entries');
            $table->foreign('entry_id')->references('id')->on('entries');
            $table->string('text', 255)->nullable()->comment('Текст коментария к заявке');
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
        Schema::dropIfExists('entry_reviews');
    }
}
