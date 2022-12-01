<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\QueueModuleSeeder;
use Illuminate\Support\Facades\DB;

class AddQueueModuleToModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')
            ->where('id', 25)
            ->update([
                'plural'    => 'queues',
                'singular'  => 'queue',
                'name'      => 'Черги',
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
            ->where('id', 25)
            ->update([
                'plural'    => 'events',
                'singular'  => 'event',
                'name'      => 'Записи у е-чергу',
            ]);
    }
}
