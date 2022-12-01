<?php

namespace Tests\Feature\Admin;

use App\Models\LinkCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class LinkCategoryControllerTest extends TestCase
{
    public function testLanguages()
    {
        $linkCategory = LinkCategory::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/link_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($linkCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $linkCategories = LinkCategory::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/link_categories');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $linkCategories->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'parent_id' => $item->parent_id,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'link_category_id' => $translation->link_category_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $parent = LinkCategory::factory()->create();

        $data = [
            'parent_id' => (string)$parent->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/link_category', $data);

        $linkCategory = LinkCategory::get()[1];

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $linkCategory->id,
                'parent_id' => $parent->id,
                'translations' => $linkCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'link_category_id' => $translation->link_category_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('link_categories', [
            'id' => $linkCategory->id,
            'parent_id' => $parent->id,
        ]);

        foreach ($linkCategory->translations as $translation) {
            $this->assertDatabaseHas('link_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'link_category_id' => $translation->link_category_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $parent = LinkCategory::factory()->create();

        $linkCategory = LinkCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/link_category/' . $linkCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $linkCategory->id,
                'parent_id' => $parent->id,
                'translations' => $linkCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'link_category_id' => $translation->link_category_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $linkCategory = LinkCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/link_category/' . $linkCategory->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($linkCategory);
    }

    public function testUpdateAction()
    {
        $parent = LinkCategory::factory()->create();

        $linkCategory = LinkCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/link_category/' . $linkCategory->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $linkCategory->id,
                    'parent_id' => $parent->id,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'link_category_id' => $linkCategory->id,
                            'name' => $data['name:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'link_category_id' => $linkCategory->id,
                            'name' => $data['name:uk'],
                        ]
                    ]
                ]
            );

        foreach ($linkCategory->translations as $translation) {
            $this->assertDatabaseHas('link_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'link_category_id' => $translation->link_category_id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        LinkCategory::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/link_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        LinkCategory::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/link_category/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        LinkCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/link_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
