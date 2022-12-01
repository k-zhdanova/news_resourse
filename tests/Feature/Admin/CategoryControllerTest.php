<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Sector;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    public function testLanguages()
    {
        Sector::factory()->create();
        $category = Category::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', 'api/v1/categories?lang', ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);

            $this->assertEquals($category->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $sector = Sector::factory()->create();

        $categories = Category::factory()->count(10)->create([
            'sector_id' => $sector->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/categories');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $categories->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uri' => $item->uri,
                        'sector_id' => $item->sector_id,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'category_id' => $translation->category_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $sector = Sector::factory()->create();

        $data = [
            'sector_id' => (string)$sector->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/category', $data);

        $category = Category::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $category->id,
                'sector_id' => $sector->id,
                'uri' => $category->uri,
                'translations' => $category->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'category_id' => $translation->category_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'uri' => $category->uri,
        ]);

        foreach ($category->translations as $translation) {
            $this->assertDatabaseHas('category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'category_id' => $translation->category_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $sector = Sector::factory()->create();

        $category = Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/category/' . $category->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $category->id,
                'sector_id' => $sector->id,
                'uri' => $category->uri,
                'translations' => $category->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'category_id' => $translation->category_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $this->actingAs($this->user)
            ->delete('api/v1/category/' . $category->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($category);
    }

    public function testUpdateAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/category/' . $category->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $category->id,
                    'uri' => $category->uri,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'category_id' => $category->id,
                            'name' => $data['name:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'category_id' => $category->id,
                            'name' => $data['name:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                        ]
                    ]
                ]
            );

        foreach ($category->translations as $translation) {
            $this->assertDatabaseHas('category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'category_id' => $translation->category_id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        $sector = Sector::factory()->create();
        Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $this->actingAs($this->user)
            ->get('api/v1/category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        $sector = Sector::factory()->create();
        Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/category/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        $sector = Sector::factory()->create();
        Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $this->actingAs($this->user)
            ->delete('api/v1/category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
