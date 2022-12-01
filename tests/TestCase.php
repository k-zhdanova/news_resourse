<?php

namespace Tests;

use Exception;
use Faker\Factory;
use App\Models\User;
use App\Models\Role;
use Faker\Generator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication, DatabaseMigrations;

    private Generator $faker;

    public function setUp(): void
    {

        parent::setUp();
        $this->faker = Factory::create();
        Artisan::call('migrate:refresh');

        $this->user = User::factory()
            ->has(Role::factory())
            ->create();
    }

    public function __get($key)
    {

        if ($key === 'faker')
            return $this->faker;
        throw new Exception('Unknown Key Requested');
    }
}
