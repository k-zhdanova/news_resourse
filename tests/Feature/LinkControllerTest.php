<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\LinkCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    public function testLanguages()
    {
        $link = Link::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/links?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($link->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $linkCategory = LinkCategory::factory()->create();
        $links = Link::factory()->count(10)->create([
            'category_id' => $linkCategory->id
        ]);

        $response = $this->get('api/v1/web/links');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $links->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'category_id' => $item->category_id,
                        'follow' => $item->follow,
                        'link' => $item->link,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'link_id' => $translation->link_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $linkCategory = LinkCategory::factory()->create();

        $link = Link::factory()->create([
            'category_id' => $linkCategory->id,
        ]);

        $response = $this->get('api/v1/web/link/' . $linkCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $link->id,
                'category_id' => $linkCategory->id,
                'link' => $link->link,
                'follow' => $link->follow,
                'translations' => $link->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'link_id' => $translation->link_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Link::factory()->create();

        $this->get('api/v1/web/link/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
