<?php

namespace Tests\Feature;

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
            $response = $this->json('get', "api/v1/web/link_categories?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($linkCategory->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $linkCategories = LinkCategory::factory()->count(10)->create();

        $response = $this->get('api/v1/web/link_categories');

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

    public function testShowAction()
    {
        $parent = LinkCategory::factory()->create();

        $linkCategory = LinkCategory::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $response = $this->get('api/v1/web/link_category/' . $linkCategory->id);

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

    public function testShowForMissingData()
    {
        LinkCategory::factory()->create();

        $this->get('api/v1/web/link_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
