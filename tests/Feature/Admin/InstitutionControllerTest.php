<?php

namespace Tests\Feature\Admin;

use App\Models\Institution;
use Illuminate\Http\Response;
use Tests\TestCase;

class InstitutionControllerTest extends TestCase
{
    public function testLanguages()
    {
        $institution = Institution::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/institutions?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($institution->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $institutions = Institution::factory()->count(4)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/institutions');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $institutions->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uri' => $item->uri,
                        'emails' => $item->emails,
                        'phones' => $item->phones,
                        'website' => $item->website,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'institution_id' => $translation->institution_id,
                                'address' => $translation->address,
                                'schedule' => $translation->schedule,
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
            'address:en' => $this->faker->text(250),
            'address:uk' => $this->faker->text(250),
            'schedule:en' => $this->faker->text(250),
            'schedule:uk' => $this->faker->text(250),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/institution', $data);

        $institution = Institution::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $institution->id,
                'uri' => $institution->uri,
                'translations' => $institution->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'institution_id' => $translation->institution_id,
                        'address' => $translation->address,
                        'schedule' => $translation->schedule,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('institutions', [
            'id' => $institution->id,
            'uri' => $institution->uri,
        ]);

        foreach ($institution->translations as $translation) {
            $this->assertDatabaseHas('institution_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'institution_id' => $translation->institution_id,
                'address' => $translation->address,
                'schedule' => $translation->schedule,
            ]);
        }
    }

    public function testShowAction()
    {
        $institution = Institution::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/institution/' . $institution->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $institution->id,
                'uri' => $institution->uri,
                'translations' => $institution->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'institution_id' => $translation->institution_id,
                        'address' => $translation->address,
                        'schedule' => $translation->schedule,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $institution = Institution::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/institution/' . $institution->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($institution);
    }

    public function testUpdateAction()
    {
        $institution = Institution::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
            'address:en' => $this->faker->text(250),
            'address:uk' => $this->faker->text(250),
            'schedule:en' => $this->faker->text(250),
            'schedule:uk' => $this->faker->text(250),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/institution/' . $institution->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $institution->id,
                    'uri' => $institution->uri,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'name' => $data['name:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                            'institution_id' => $institution->id,
                            'address' => $data['address:en'],
                            'schedule' => $data['schedule:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'name' => $data['name:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                            'institution_id' => $institution->id,
                            'address' => $data['address:uk'],
                            'schedule' => $data['schedule:uk'],
                        ]
                    ]
                ]
            );

        foreach ($institution->translations as $translation) {
            $this->assertDatabaseHas('institution_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'address' => $data['address:' . $translation->locale],
                'schedule' => $data['schedule:' . $translation->locale],
                'institution_id' => $institution->id
            ]);
        }
    }

    public function testShowForMissingData()
    {
        Institution::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/institution/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Institution::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/institution/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Institution::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/institution/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
