<?php

namespace Tests\Feature;

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
            $response = $this->json('get', "api/v1/web/news?lang", ['lang' => $lang])
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

        $response = $this->get('api/v1/web/news');

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

    public function testShowAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $news = News::factory()->create([
            'category_id' => $newsCategory->id,
        ]);

        $response = $this->get('api/v1/web/news/' . $news->id);

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

    public function testShowPreviewAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $news = News::factory()->create([
            'category_id' => $newsCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/web/news/preview/' . $news->id);

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

    public function testShowForMissingData()
    {
        News::factory()->create();

        $this->get('api/v1/web/news/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
