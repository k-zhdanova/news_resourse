<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_role = new UserRole();

        $user_role->fill([
            'user_id' => 1,
            'role_id' => 1
        ]);

        $user_role->disableLogging();

        $user_role->save();

        $user_role->enableLogging();
    }
}
