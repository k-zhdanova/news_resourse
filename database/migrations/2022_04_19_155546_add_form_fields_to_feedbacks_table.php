<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormFieldsToFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feed_backs', function (Blueprint $table) {
            $table->timestamp('date')->nullable()->after('status');
            $table->integer('age')->nullable()->after('date');
            $table->enum('sex', ['male', 'female'])->after('age');
            $table->bigInteger('service_id')->nullable()->unsigned()->comment('ID услуги из таблицы services')->after('sex');
            $table->foreign('service_id')->references('id')->on('services')->constrained()->onDelete('cascade');
            $table->tinyInteger('is_satisfied')->nullable()->after('service_id');
            $table->tinyInteger('reception_friendly')->default(0)->after('is_satisfied');
            $table->tinyInteger('reception_competent')->default(0)->after('reception_friendly');
            $table->tinyInteger('center_friendly')->default(0)->after('reception_competent');
            $table->tinyInteger('center_competent')->default(0)->after('center_friendly');
            $table->tinyInteger('impression')->default(0)->after('center_competent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feed_backs', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('age');
            $table->dropColumn('sex');
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->dropColumn('is_satisfied');
            $table->dropColumn('reception_friendly');
            $table->dropColumn('reception_competent');
            $table->dropColumn('center_friendly');
            $table->dropColumn('center_competent');
            $table->dropColumn('impression');
        });
    }
}
