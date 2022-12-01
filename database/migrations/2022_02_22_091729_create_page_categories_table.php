<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->unsigned()->comment('ID категории из таблицы page_categories');
            $table->foreign('parent_id')->references('id')->on('page_categories')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_category_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('page_category_id')->unsigned();
            $table->unique(['page_category_id', 'locale']);
            $table->foreign('page_category_id')->references('id')->on('page_categories')->onDelete('cascade');
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
        Schema::dropIfExists('page_category_translations');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();
    }
}
