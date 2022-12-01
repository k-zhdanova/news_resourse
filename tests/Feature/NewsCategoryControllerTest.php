<?php

namespace Tests\Feature;

use App\Models\NewsCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class NewsCategoryControllerTest extends TestCase
{
    public function testLanguages()
    {
        $newsCategory = NewsCategory::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/news_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($newsCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $newsCategories = NewsCategory::factory()->count(10)->create();

        $response = $this->get('api/v1/web/news_categories');

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

    public function testShowAction()
    {
        $newsCategory = NewsCategory::factory()->create();

        $response = $this->get('api/v1/web/news_category/' . $newsCategory->id);

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

    public function testShowForMissingData()
    {
        NewsCategory::factory()->create();

        $this->get('api/v1/web/news_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
