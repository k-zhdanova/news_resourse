<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLawCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->unsigned()->comment('ID категории из таблицы law_categories');
            $table->foreign('parent_id')->references('id')->on('law_categories')->constrained()->onDelete('cascade');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('law_category_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('law_category_id')->unsigned();
            $table->unique(['law_category_id', 'locale']);
            $table->foreign('law_category_id')->references('id')->on('law_categories')->onDelete('cascade');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('law_category_translations');
        Schema::dropIfExists('law_categories');
        Schema::enableForeignKeyConstraints();
    }
}
