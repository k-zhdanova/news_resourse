<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->after('is_online', function ($table) {
                $table->string('file1')->nullable()->comment('Путь к файлу 1');
                $table->string('file2')->nullable()->comment('Путь к файлу 2');
                $table->tinyInteger('is_free')->default(1)->comment('Бесплатно');
            });
        });

        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('filename1')->nullable()->comment('Название файла 1');
            $table->string('filename2')->nullable()->comment('Название файла 2');
            $table->string('place')->nullable()->comment('Место оказания услуги, по умолчанию ЦНАП');
            $table->string('term', 1000)->nullable()->comment('Срок');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('file1');
            $table->dropColumn('file2');
            $table->dropColumn('is_free');
        });

        Schema::table('service_translations', function (Blueprint $table) {
            $table->dropColumn('filename1');
            $table->dropColumn('filename2');
            $table->dropColumn('place');
            $table->dropColumn('term');
        });
    }
}
