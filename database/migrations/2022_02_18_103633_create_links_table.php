<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id')->nullable()->unsigned();
            $table->foreign('category_id')->references('id')->on('link_categories');
            $table->string('link')->comment('Ссылка');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->tinyInteger('follow')->nullable()->comment('1 или 0');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('link_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('link_id')->unsigned();
            $table->unique(['link_id', 'locale']);
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
            $table->string('name', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_translations');
        Schema::dropIfExists('links');
    }
}
