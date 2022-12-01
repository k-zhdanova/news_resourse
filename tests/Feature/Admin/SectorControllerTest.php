<?php

namespace Tests\Feature\Admin;

use App\Models\Sector;
use Illuminate\Http\Response;
use Tests\TestCase;

class SectorControllerTest extends TestCase
{
    public function testLanguagesNews()
    {
        $sector = Sector::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/sectors?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($sector->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $sectors = Sector::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/sectors');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $sectors->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uri' => $item->uri,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'sector_id' => $translation->sector_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }


    public function testCreateAction()
    {
        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/sector', $data);

        $sector = Sector::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $sector->id,
                'uri' => $sector->uri,
                'translations' => $sector->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'sector_id' => $translation->sector_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('sectors', [
            'id' => $sector->id,
            'uri' => $sector->uri,
        ]);

        foreach ($sector->translations as $translation) {
            $this->assertDatabaseHas('sector_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'sector_id' => $translation->sector_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $sector = Sector::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/sector/' . $sector->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $sector->id,
                'uri' => $sector->uri,
                'translations' => $sector->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'sector_id' => $translation->sector_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $sector = Sector::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/sector/' . $sector->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($sector);
    }

    public function testUpdateAction()
    {
        $sector = Sector::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/sector/' . $sector->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $sector->id,
                    'uri' => $sector->uri,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'name' => $data['name:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                            'sector_id' => $sector->id,

                        ],
                        [
                            'locale' => 'uk',
                            'name' => $data['name:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                            'sector_id' => $sector->id,
                        ]
                    ]
                ]
            );

        foreach ($sector->translations as $translation) {
            $this->assertDatabaseHas('sector_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'sector_id' => $sector->id
            ]);
        }
    }

    public function testShowForMissingData()
    {
        Sector::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/sector/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Sector::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/sector/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Sector::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/sector/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
