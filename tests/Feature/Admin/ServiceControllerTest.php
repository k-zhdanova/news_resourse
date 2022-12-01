<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ------------------- Need to re-write -------------------
class ServiceControllerTest1 extends TestCase
{
    private const PDF_DATA = 'data:application/pdf;base64,JVBERi0xLjcNCiW1tbW1DQpBJHAHmXSHoWJBMB4ca5afoI9dp/CUu0rmTpmxwCkrK+3UORUheNfO57jUJcgZpjSm/5+ULvhtlHOjm5ZH+atpCJ6Osqpp7UOjFGx4GRoj1EOkcuN1P3lY9hzEEiNE8hn+laM4';

    public function testLanguagesService()
    {
        $service = Service::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/services?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($service->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();
        $services = Service::factory()->count(10)->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/services');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $services->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sector_id' => $item->sector_id,
                        'category_id' => $item->category_id,
                        'institution_id' => $item->institution_id,
                        'uri' => $item->uri,
                        'is_online' => $item->is_online,
                        'is_free' => $item->is_free,
                        'file1' => $item->file1,
                        'file2' => $item->file2,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'text' => $translation->text,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'service_id' => $translation->service_id,
                                'filename1' => $translation->filename1,
                                'filename2' => $translation->filename2,
                                'place' => $translation->place,
                                'term' => $translation->term,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $data = [
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(100),
            'text:uk' => $this->faker->text(100),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
            'is_online' => $this->faker->numberBetween(0, 1),
            "is_free" => $this->faker->numberBetween(0, 1),
            "file1" => $this->faker->text(255),
            "file2" => $this->faker->text(255),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "place:en" => $this->faker->text(255),
            "place:uk" => $this->faker->text(255),
            "term:en" => $this->faker->text(255),
            "term:uk" => $this->faker->text(255),
            "file_1" => self::PDF_DATA,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/service', $data);

        $service = Service::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $service->id,
                'sector_id' => $service->sector_id,
                'category_id' => $service->category_id,
                'institution_id' => $service->institution_id,
                'uri' => $service->uri,
                'is_online' => $service->is_online,
                'is_free' => $service->is_free,
                'file1' => $service->file1,
                'file2' => $service->file2,
                'translations' => $service->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'service_id' => $translation->service_id,
                        'filename1' => $translation->filename1,
                        'filename2' => $translation->filename2,
                        'place' => $translation->place,
                        'term' => $translation->term,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'category_id' => $service->category_id,
            'institution_id' => $service->institution_id,
            'uri' => $service->uri,
            'is_online' => $service->is_online,
            'is_free' => $service->is_free,
            'file1' => $service->file1,
            'file2' => $service->file2,
        ]);

        foreach ($service->translations as $translation) {
            $this->assertDatabaseHas('service_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'text' => $translation->text,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'service_id' => $translation->service_id,
                'filename1' => $translation->filename1,
                'filename2' => $translation->filename2,
                'place' => $translation->place,
                'term' => $translation->term,
            ]);
        }

        Storage::disk('public')->assertExists(config('custom.service_files_path') . '/' . $service->file1);
    }

    public function testShowAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $service = Service::factory()->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/service/' . $service->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $service->id,
                'sector_id' => $service->sector_id,
                'category_id' => $service->category_id,
                'institution_id' => $service->institution_id,
                'uri' => $service->uri,
                'is_online' => $service->is_online,
                'is_free' => $service->is_free,
                'file1' => $service->file1,
                'file2' => $service->file2,
                'translations' => $service->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'service_id' => $translation->service_id,
                        'filename1' => $translation->filename1,
                        'filename2' => $translation->filename2,
                        'place' => $translation->place,
                        'term' => $translation->term,
                    ];
                })->toArray()
            ]);
    }

    public function testServiceDeleteAction()
    {
        $service = Service::factory()->create();

        $this->actingAs($this->user)
            ->put('api/v1/service/' . $service->id, ['file_1' => self::PDF_DATA]);

        $service->refresh();

        Storage::disk('public')->assertExists(config('custom.service_files_path') . '/' . $service->file1);

        $this->actingAs($this->user)
            ->delete('api/v1/service/' . $service->id)
            ->assertStatus(Response::HTTP_OK);

        Storage::disk('public')->assertMissing(config('custom.service_files_path') . '/' . $service->file1);
        $this->assertSoftDeleted($service);
    }

    public function testUpdateAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $service = Service::factory()->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(15),
            'text:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "place:en" => $this->faker->text(255),
            "place:uk" => $this->faker->text(255),
            "term:en" => $this->faker->text(255),
            "term:uk" => $this->faker->text(255),
            "file_1" => self::PDF_DATA,
        ];

        $response = $this->actingAs($this->user)
            ->put('api/v1/service/' . $service->id, $data);

        $service->refresh();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $service->id,
                    'sector_id' => $service->sector_id,
                    'category_id' => $service->category_id,
                    'institution_id' => $service->institution_id,
                    'uri' => $service->uri,
                    'is_online' => $service->is_online,
                    'is_free' => $service->is_free,
                    'file1' => $service->file1,
                    'file2' => $service->file2,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'service_id' => $service->id,
                            'name' => $data['name:en'],
                            'text' => $data['text:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                            'filename1' => $data['filename1:en'],
                            'filename2' => $data['filename2:en'],
                            'place' => $data['place:en'],
                            'term' => $data['term:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'service_id' => $service->id,
                            'name' => $data['name:uk'],
                            'text' => $data['text:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                            'filename1' => $data['filename1:uk'],
                            'filename2' => $data['filename2:uk'],
                            'place' => $data['place:uk'],
                            'term' => $data['term:uk'],
                        ]
                    ]
                ]
            );

        foreach ($service->translations as $translation) {
            $this->assertDatabaseHas('service_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'text' => $data['text:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'service_id' => $service->id,
                'filename1' => $data['filename1:' . $translation->locale],
                'filename2' => $data['filename2:' . $translation->locale],
                'place' => $data['place:' . $translation->locale],
                'term' => $data['term:' . $translation->locale],
            ]);
        }

        Storage::disk('public')->assertExists(config('custom.service_files_path') . '/' . $service->file1);
    }

    public function testShowForMissingData()
    {
        Service::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/service/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Service::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/service/0', $data)
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
