<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();

        $user->fill([
            'firstname' => 'Admin',
            'email'     => 'admin'
        ]);

        $user->disableLogging();

        $user->password = Hash::make('111111');
        $user->save();

        $user->enableLogging();
    }
}
