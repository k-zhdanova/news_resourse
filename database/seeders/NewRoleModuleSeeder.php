<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewRoleModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 4, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 5, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 6, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 7, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 8, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 9, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 10, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 11, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 12, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 13, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 14, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 15, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 16, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 17, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 18, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 19, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 20, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        DB::table('roles_modules_rel')->insert([
            'role_id' => 1, 
            'module_id' => 21, 
            'scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
    }
}
