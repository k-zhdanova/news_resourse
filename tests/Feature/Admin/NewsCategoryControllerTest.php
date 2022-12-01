<?php

namespace Tests\Feature\Admin;

use App\Models\NewsCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

use function PHPUnit\Framework\assertJson;

class NewsCategoryControllerTest extends TestCase
{
    public function testLanguages()
    {
        $newsCategory = NewsCategory::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/news_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($newsCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $newsCategories = NewsCategory::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/news_categories');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $newsCategories->map(function ($item) {
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
                                'news_category_id' => $translation->news_category_id,
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
            ->postJson('api/v1/news_category', $data);

        $newsCategory = NewsCategory::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $newsCategory->id,
                'uri' => $newsCategory->uri,
                'translations' => $newsCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'news_category_id' => $translation->news_category_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('news_categories', [
            'id' => $newsCategory->id,
            'uri' => $newsCategory->uri,
        ]);

        foreach ($newsCategory->translations as $translation) {
            $this->assertDatabaseHas('news_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'news_category_id' => $translation->news_category_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/news_category/' . $newsCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $newsCategory->id,
                'uri' => $newsCategory->uri,
                'translations' => $newsCategory->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'news_category_id' => $translation->news_category_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/news_category/' . $newsCategory->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($newsCategory);
    }


    public function testUpdateAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/news_category/' . $newsCategory->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $newsCategory->id,
                    'uri' => $newsCategory->uri,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'news_category_id' => $newsCategory->id,
                            'name' => $data['name:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'news_category_id' => $newsCategory->id,
                            'name' => $data['name:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                        ]
                    ]
                ]
            );

        foreach ($newsCategory->translations as $translation) {
            $this->assertDatabaseHas('news_category_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'news_category_id' => $translation->news_category_id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        NewsCategory::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/news_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        NewsCategory::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/news_category/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        NewsCategory::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/news_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
