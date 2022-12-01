<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeFeedbackModuleAtModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')
            ->where('id', 20)
            ->update([
                'plural'    => 'feedbacks',
                'singular'  => 'feedback',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modules')
            ->where('id', 20)
            ->update([
                'plural'    => 'reviews',
                'singular'  => 'review',
            ]);
    }
}
