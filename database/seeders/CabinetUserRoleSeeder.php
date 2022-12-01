<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CabinetUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        $user = new User();

        $user->fill([
            'firstname' => 'Cabinet User',
            'email'     => 'user'
        ]);

        $user->disableLogging();

        $user->password = Hash::make('111111');
        $user->save();

        $user->enableLogging();

        DB::table('roles')->insert([
            'name'       => 'Користувач кабінету',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $user_role = new UserRole();

        $user_role->fill([
            'user_id' => 2,
            'role_id' => 3
        ]);

        $user_role->disableLogging();

        $user_role->save();

        $user_role->enableLogging();
    }
}
