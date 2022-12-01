<?php

namespace Tests\Feature\Admin;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\Response;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    public function testIndexAction()
    {
        Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create();

        $this->actingAs($this->user)
            ->json('get', 'api/v1/roles')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data" => [
                        '*' => [
                            "id",
                            "name",
                            "created_at",
                            "updated_at",
                        ],
                    ]
                ]
            );
    }

    public function testCreateAction()
    {
        Module::factory()
            ->hasAttached(
                Role::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create();

        $data = [
            'name' => $this->faker->text(15),
        ];
        $this->actingAs($this->user)
            ->json('post', 'api/v1/role', $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(
                [
                    "name",
                    "updated_at",
                    "created_at",
                    "id"
                ]
            );

        $this->assertDatabaseHas('roles', ['name' => $data['name']]);
    }

    public function testShowAction()
    {
        $data = Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create(['name' => $this->faker->text(15)]);

        $this->actingAs($this->user)
            ->json('get', "api/v1/role/$data->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $data->id,
                    "name" => $data['name'],
                ]
            );
    }

    public function testDeleteAction()
    {
        $name =
            [
                'name' => $this->faker->text(10),
                'deleted_at' => null
            ];
        $data = Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create($name);

        $this->actingAs($this->user)
            ->json('delete', "api/v1/role/$data->id")
            ->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseMissing('roles', $name);
    }

    public function testUpdateAction()
    {
        $role = Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create();

        $name =
            [
                'name' => $this->faker->text(10),
            ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/role/$role->id", $name)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $role->id,
                    "name" => $name['name'],
                ]
            );
    }

    public function testShowForMissingData()
    {
        Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create();
        $this->actingAs($this->user)
            ->json('get', "api/v1/role/0")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {

        $data = [
            'name' => $this->faker->text(10),

        ];
        Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create($data);

        $this->actingAs($this->user)
            ->json('put', "/api/v1/role/0", $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Role::factory()
            ->hasAttached(
                Module::factory(),
                ['scopes' => '{"read":"1","create":"1","update":"1","delete":"1","publish":"0"}']
            )
            ->create();
        $this->actingAs($this->user)
            ->json('delete', 'api/v1/role/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
