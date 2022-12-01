<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->unsigned()->comment('ID категории из таблицы link_categories');
            $table->foreign('parent_id')->references('id')->on('link_categories')->constrained()->onDelete('cascade');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('link_category_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('link_category_id')->unsigned();
            $table->unique(['link_category_id', 'locale']);
            $table->foreign('link_category_id')->references('id')->on('link_categories')->onDelete('cascade');
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
        Schema::dropIfExists('link_category_translations');
        Schema::dropIfExists('link_categories');
        Schema::enableForeignKeyConstraints();
    }
}
