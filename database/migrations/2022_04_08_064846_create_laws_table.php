<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laws', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id')->nullable()->unsigned();
            $table->foreign('category_id')->references('id')->on('law_categories');
            $table->string('link')->comment('Ссылка');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->tinyInteger('follow')->nullable()->comment('1 или 0');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('law_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('law_id')->unsigned();
            $table->unique(['law_id', 'locale']);
            $table->foreign('law_id')->references('id')->on('laws')->onDelete('cascade');
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
        Schema::dropIfExists('law_translations');
        Schema::dropIfExists('laws');
        Schema::enableForeignKeyConstraints();
    }
}
