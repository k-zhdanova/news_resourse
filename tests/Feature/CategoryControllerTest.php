<?php

namespace Tests\Feature;

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
            $response = $this->json('get', 'api/v1/web/categories?lang', ['lang' => $lang])
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

        $response = $this->get('api/v1/web/categories');

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

    public function testShowAction()
    {
        $sector = Sector::factory()->create();

        $category = Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $response = $this->get('api/v1/web/category/' . $category->id);

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

    public function testShowForMissingData()
    {
        $sector = Sector::factory()->create();
        Category::factory()->create([
            'sector_id' => $sector->id
        ]);

        $this->get('api/v1/web/category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
