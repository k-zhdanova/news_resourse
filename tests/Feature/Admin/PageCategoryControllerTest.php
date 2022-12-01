<?php

namespace Tests\Feature\Admin;

use App\Models\PageCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class PageCategoryControllerTest extends TestCase
{
    public function testLanguages()
    {
        $pageCategory = PageCategory::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/page_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($pageCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $pageCategories = PageCategory::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/page_categories');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $pageCategories->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'parent_id' => $item->parent_id,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'page_category_id' => $translation->page_category_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $parent = PageCategory::factory()->create();

        $data = [
            'parent_id' => (string)$parent->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/page_category', $data);

        $pageCategory = PageCategory::get()[1];

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $pageCategory->id,
                'parent_id' => $parent->id,
                'translations' => $pageCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'page_category_id' => $translation->page_category_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('page_categories', [
            'id' => $pageCategory->id,
            'parent_id' => $parent->id,
        ]);

        foreach ($pageCategory->translations as $translation) {
            $this->assertDatabaseHas('page_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'page_category_id' => $translation->page_category_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $parent = PageCategory::factory()->create();

        $pageCategory = PageCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/page_category/' . $pageCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $pageCategory->id,
                'parent_id' => $parent->id,
                'translations' => $pageCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'page_category_id' => $translation->page_category_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/page_category/' . $pageCategory->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($pageCategory);
    }

    public function testUpdateAction()
    {
        $parent = PageCategory::factory()->create();

        $pageCategory = PageCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/page_category/' . $pageCategory->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $pageCategory->id,
                    'parent_id' => $parent->id,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'page_category_id' => $pageCategory->id,
                            'name' => $data['name:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'page_category_id' => $pageCategory->id,
                            'name' => $data['name:uk'],
                        ]
                    ]
                ]
            );

        foreach ($pageCategory->translations as $translation) {
            $this->assertDatabaseHas('page_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'page_category_id' => $translation->page_category_id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        PageCategory::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/page_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        PageCategory::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/page_category/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        PageCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/page_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
