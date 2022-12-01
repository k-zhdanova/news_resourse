<?php

namespace Tests\Feature\Admin;

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
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/links?lang", ['lang' => $lang])
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

        $response = $this->actingAs($this->user)
            ->get('api/v1/links');

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

    public function testCreateAction()
    {
        $linkCategory = LinkCategory::factory()->create();

        $data = [
            'category_id' => (string)$linkCategory->id,
            'link' => $this->faker->url(),
            'follow' => $this->faker->numberBetween(0, 1),
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/link', $data);

        $link = Link::first();

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

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'category_id' => $linkCategory->id,
            'link' => $link->link,
            'follow' => $link->follow,
        ]);

        foreach ($link->translations as $translation) {
            $this->assertDatabaseHas('link_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'link_id' => $translation->link_id,
            ]);
        }
    }

    public function testShowAction()
    {
        $linkCategory = LinkCategory::factory()->create();

        $link = Link::factory()->create([
            'category_id' => $linkCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/link/' . $linkCategory->id);

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

    public function testDeleteAction()
    {
        $link = Link::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/link/' . $link->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($link);
    }

    public function testUpdateAction()
    {
        $linkCategory = LinkCategory::factory()->create();

        $link = Link::factory()->create([
            'category_id' => $linkCategory->id,
        ]);

        $data = [
            'link' => $this->faker->url(),
            'follow' => $this->faker->numberBetween(0, 1),
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/link/' . $link->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $link->id,
                    'category_id' => $linkCategory->id,
                    'link' => $data['link'],
                    'follow' => $data['follow'],
                    'translations' => [
                        [
                            'locale' => 'en',
                            'link_id' => $link->id,
                            'name' => $data['name:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'link_id' => $link->id,
                            'name' => $data['name:uk'],
                        ]
                    ]
                ]
            );

        foreach ($link->translations as $translation) {
            $this->assertDatabaseHas('link_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'link_id' => $link->id,
            ]);
        }
    }

    public function testShowForMissingData()
    {
        Link::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/link/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Link::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/link/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Link::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/link/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
