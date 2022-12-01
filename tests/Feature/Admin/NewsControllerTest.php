<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    public function testLanguagesNews()
    {
        $news = News::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/news?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($news->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $newsCategory = NewsCategory::factory()->create();
        $news = News::factory()->count(10)->create([
            'category_id' => $newsCategory->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/news');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $news->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'category_id' => $item->category_id,
                        'uri' => $item->uri,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'text' => $translation->text,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'news_id' => $translation->news_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $data = [
            'category_id' => $newsCategory->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(100),
            'text:uk' => $this->faker->text(100),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/news', $data);

        $news = News::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $news->id,
                'category_id' => $newsCategory->id,
                'uri' => $news->uri,
                'translations' => $news->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'news_id' => $translation->news_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'category_id' => $newsCategory->id,
            'uri' => $news->uri,
        ]);

        foreach ($news->translations as $translation) {
            $this->assertDatabaseHas('news_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'text' => $translation->text,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'news_id' => $translation->news_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $news = News::factory()->create([
            'category_id' => $newsCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/news/' . $news->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $news->id,
                'category_id' => $newsCategory->id,
                'uri' => $news->uri,
                'translations' => $news->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'news_id' => $translation->news_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $news = News::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/news/' . $news->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($news);
    }

    public function testUpdateAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $news = News::factory()->create([
            'category_id' => $newsCategory->id,
        ]);

        $data = [
            'category_id' => $newsCategory->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(15),
            'text:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/news/' . $news->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $news->id,
                    'category_id' => $newsCategory->id,
                    'uri' => $news->uri,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'news_id' => $news->id,
                            'name' => $data['name:en'],
                            'text' => $data['text:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'news_id' => $news->id,
                            'name' => $data['name:uk'],
                            'text' => $data['text:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                        ]
                    ]
                ]
            );

        foreach ($news->translations as $translation) {
            $this->assertDatabaseHas('news_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'text' => $data['text:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'news_id' => $news->id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        News::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/news/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        News::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/news/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        News::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/news/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
