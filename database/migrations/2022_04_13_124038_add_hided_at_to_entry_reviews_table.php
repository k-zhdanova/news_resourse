<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHidedAtToEntryReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entry_reviews', function (Blueprint $table) {
            $table->timestamp('hided_at')->nullable()->comment('Дата отключения')->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entry_reviews', function (Blueprint $table) {
            $table->dropColumn('hided_at');
        });
    }
}
