<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\NewsSeeder;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('uri', 255);
            $table->string('image', 40)->nullable();
            $table->tinyInteger('is_pinned')->default(0)->comment('Закреплена на главной');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('news_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('news_id')->unsigned();
            $table->unique(['news_id', 'locale']);
            $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('text')->nullable()->comment('Текст новости');
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_translations');
        Schema::dropIfExists('news');
    }
}
