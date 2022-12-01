<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->comment('Номер (код) послуги');
            $table->bigInteger('sector_id')->nullable()->unsigned()->comment('ID сферы услуг из таблицы sectors');
            $table->foreign('sector_id')->references('id')->on('sectors');
            $table->bigInteger('category_id')->nullable()->unsigned()->comment('ID категории услуг из таблицы categories');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->bigInteger('institution_id')->nullable()->unsigned()->comment('ID субъекта оказания услуг из таблицы institutions');
            $table->foreign('institution_id')->references('id')->on('institutions');
            $table->string('uri', 255);
            $table->tinyInteger('is_online')->default(0)->comment('Возможность заказать услугу онлайн');
            $table->timestamp('published_at')->nullable()->comment('Дата публикации');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->index();
            $table->bigInteger('service_id')->unsigned();
            $table->unique(['service_id', 'locale']);
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('text')->nullable()->comment('Полное описание услуги');
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
        Schema::dropIfExists('service_translations');
        Schema::dropIfExists('services');
    }
}
