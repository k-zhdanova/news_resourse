<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testIndexAction()
    {
        User::factory()
            ->has(Role::factory())
            ->create();

        $this->actingAs($this->user)
            ->json('get', 'api/v1/users')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data" => [
                        '*' => [
                            "id",
                            "firstname",
                            "lastname",
                            "email",
                            "remember_token",
                            "created_at",
                            "updated_at",
                        ],
                    ]
                ]
            );
    }

    public function testCreateAction()
    {
        Role::factory()->create();
        $data = [
            'firstname' => "yarik",
            'lastname' => "vitya",
            'email' => $this->faker->email,
            'password' => 123456,
            'password_confirmation' => 123456
        ];
        $this->actingAs($this->user)
            ->json('post', 'api/v1/user', $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(
                [
                    "firstname",
                    "lastname",
                    "email",
                    "updated_at",
                    "created_at",
                    "id"
                ]
            );

        $this->assertDatabaseHas('users', ['firstname' => $data['firstname'], 'lastname' => $data['lastname']]);
    }

    public function testShowAction()
    {
        $data = User::factory()
            ->has(Role::factory())
            ->create([
                'firstname' => $this->faker->firstName,
            ]);

        $this->actingAs($this->user)
            ->json('get', "api/v1/user/$data->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $data->id,
                    "firstname" => $data['firstname'],
                ]
            );
    }

    public function testDeleteAction()
    {
        $name = [
            'firstname' => $this->faker->text(10),
        ];
        $data = User::factory()
            ->has(Role::factory())
            ->create($name);

        $this->actingAs($this->user)
            ->json('delete', "api/v1/user/$data->id")
            ->assertStatus(Response::HTTP_OK);
        $this->assertSoftDeleted($data);
    }

    public function testUpdateAction()
    {
        $user = User::factory()
            ->has(Role::factory())
            ->create();
        $name =
            [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'email' => $this->faker->email,
                'password' => 123456,
                'password_confirmation' => 123456
            ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/user/$user->id", $name)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $user->id,
                    "firstname" => $name['firstname'],
                    "lastname" => $name['lastname'],
                    "email" => $name['email'],
                ]
            );
    }

    public function testShowForMissingData()
    {
        User::factory()
            ->has(Role::factory())
            ->create();
        $this->actingAs($this->user)
            ->json('get', "api/v1/user/5")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        $data = [
            'firstname' => $this->faker->text(10),
        ];
        User::factory()
            ->has(Role::factory())
            ->create();

        $this->actingAs($this->user)
            ->json('put', "/api/v1/user/0", $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        User::factory()
            ->has(Role::factory())
            ->create();
        $this->actingAs($this->user)
            ->json('delete', 'api/v1/user/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
