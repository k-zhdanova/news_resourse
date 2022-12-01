<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Http\Response;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    public function testLanguagesNews()
    {
        $tag = Tag::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/tags?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($tag->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $tags = Tag::factory()->count(10)->create();

        $response = $this->get('api/v1/web/tags');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $tags->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'search_count' => $item->search_count,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'tag_id' => $translation->tag_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $tag = Tag::factory()->create();

        $response = $this->get('api/v1/web/tag/' . $tag->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $tag->id,
                'search_count' => $tag->search_count,
                'translations' => $tag->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'tag_id' => $translation->tag_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Tag::factory()->create();

        $this->get('api/v1/web/tag/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
