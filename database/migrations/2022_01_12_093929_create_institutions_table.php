<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstitutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('uri', 255);
            $table->text('emails')->nullable();
            $table->text('phones')->nullable();
            $table->string('website', 255)->nullable();
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('institution_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('institution_id')->unsigned();
            $table->unique(['institution_id', 'locale']);
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('schedule')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('institution_translations');
        Schema::dropIfExists('institutions');
    }
}
