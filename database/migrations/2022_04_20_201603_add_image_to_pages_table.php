<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('image', 40)->nullable()->after('uri');
            $table->string('file1')->nullable()->comment('Путь к файлу 1');
            $table->string('file2')->nullable()->comment('Путь к файлу 2');
            $table->string('file3')->nullable()->comment('Путь к файлу 3');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->string('filename1')->nullable()->comment('Название файла 1');
            $table->string('filename2')->nullable()->comment('Название файла 2');
            $table->string('filename3')->nullable()->comment('Название файла 3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('file1');
            $table->dropColumn('file2');
            $table->dropColumn('file3');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn('filename1');
            $table->dropColumn('filename2');
            $table->dropColumn('filename3');
        });
    }
}
