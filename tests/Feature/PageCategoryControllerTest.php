<?php

namespace Tests\Feature;

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
            $response = $this->json('get', "api/v1/web/page_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($pageCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $pageCategories = PageCategory::factory()->count(10)->create();

        $response = $this->get('api/v1/web/page_categories');

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
    public function testShowAction()
    {
        $parent = PageCategory::factory()->create();

        $pageCategory = PageCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $response = $this->get('api/v1/web/page_category/' . $pageCategory->id);

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

    public function testShowForMissingData()
    {
        PageCategory::factory()->create();

        $this->get('api/v1/web/page_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
