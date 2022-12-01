<?php

namespace Tests\Feature\Admin;

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
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/tags?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($tag->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $tags = Tag::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/tags');

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

    public function testCreateAction()
    {
        $data = [
            "name:en" => $this->faker->text(15),
            "name:uk" => $this->faker->text(15),
            "search_count" => $this->faker->randomNumber(1, 100),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/tag', $data);

        $tag = Tag::first();

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

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'search_count' => $tag->search_count,
        ]);

        foreach ($tag->translations as $translation) {
            $this->assertDatabaseHas('tag_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'tag_id' => $tag->id
            ]);
        }
    }

    public function testShowAction()
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/tag/' . $tag->id);

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

    public function testDeleteAction()
    {
        $tag = Tag::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/tag/' . $tag->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($tag);
    }

    public function testUpdateAction()
    {
        $tag = Tag::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/tag/' . $tag->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $tag->id,
                    'search_count' => $tag->search_count,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'name' => $data['name:en'],
                            'tag_id' => $tag->id,

                        ],
                        [
                            'locale' => 'uk',
                            'name' => $data['name:uk'],
                            'tag_id' => $tag->id,
                        ]
                    ]
                ]
            );

        foreach ($tag->translations as $translation) {
            $this->assertDatabaseHas('tag_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'tag_id' => $tag->id
            ]);
        }
    }

    public function testShowForMissingData()
    {
        Tag::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/tag/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Tag::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/tag/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Tag::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/tag/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
