<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Module::factory()
            ->hasAttached(Role::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}'])
            ->create();
    }
}
