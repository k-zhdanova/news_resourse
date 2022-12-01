<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('uri', 255);
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('news_category_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('news_category_id')->unsigned();
            $table->unique(['news_category_id', 'locale']);
            $table->foreign('news_category_id')->references('id')->on('news_categories')->onDelete('cascade');
            $table->string('name', 255);
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('news_category_translations');
        Schema::enableForeignKeyConstraints();
    }
}
