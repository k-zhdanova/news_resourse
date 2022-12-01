<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    public function testLanguagesPage()
    {
        $page = Page::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->actingAs($this->user)
                ->json('get', "api/v1/pages?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($page->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $pageCategory = PageCategory::factory()->create();
        $pages = Page::factory()->count(10)->create([
            'category_id' => $pageCategory->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/pages');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $pages->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'category_id' => $item->category_id,
                        'uri' => $item->uri,
                        'file1' => $item->file1,
                        'file2' => $item->file2,
                        'file3' => $item->file3,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'text' => $translation->text,
                                'filename1' => $translation->filename1,
                                'filename2' => $translation->filename2,
                                'filename3' => $translation->filename3,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'page_id' => $translation->page_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $data = [
            'category_id' => (string)$pageCategory->id,
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(15),
            'text:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
            'file1' => 'data:application/pdf;base64,' . $this->faker->text(100),
            'file2' => 'data:application/pdf;base64,' . $this->faker->text(100),
            'file3' => 'data:application/pdf;base64,' . $this->faker->text(100),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "filename3:en" => $this->faker->text(255),
            "filename3:uk" => $this->faker->text(255),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/page', $data);

        $page = Page::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $page->id,
                'category_id' => $pageCategory->id,
                'uri' => $page->uri,
                'translations' => $page->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'filename1' => $translation->filename1,
                        'filename2' => $translation->filename2,
                        'filename3' => $translation->filename3,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'page_id' => $translation->page_id,
                    ];
                })->toArray()
            ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'category_id' => $pageCategory->id,
            'uri' => $page->uri,
            'file1' => $page->file1,
            'file2' => $page->file2,
            'file3' => $page->file3,
        ]);

        foreach ($page->translations as $translation) {
            $this->assertDatabaseHas('page_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $translation->name,
                'text' => $translation->text,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'page_id' => $translation->page_id,
                'filename1' => $translation->filename1,
                'filename2' => $translation->filename2,
                'filename3' => $translation->filename3,
            ]);
        }

        Storage::disk('public')->assertExists($page->file1);
    }

    public function testShowAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $page = Page::factory()->create([
            'category_id' => $pageCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/page/' . $page->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $page->id,
                'category_id' => $pageCategory->id,
                'uri' => $page->uri,
                'file1' => $page->file1,
                'file2' => $page->file2,
                'file3' => $page->file3,
                'translations' => $page->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'filename1' => $translation->filename1,
                        'filename2' => $translation->filename2,
                        'filename3' => $translation->filename3,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'page_id' => $translation->page_id,
                    ];
                })->toArray()
            ]);
    }

    public function testDeleteAction()
    {
        $page = Page::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/page/' . $page->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($page);
    }

    public function testUpdateAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $page = Page::factory()->create([
            'category_id' => $pageCategory->id,
        ]);

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
            'text:en' => $this->faker->text(15),
            'text:uk' => $this->faker->text(15),
            'meta_title:en' => $this->faker->text(15),
            'meta_title:uk' => $this->faker->text(15),
            'meta_description:en' => $this->faker->text(250),
            'meta_description:uk' => $this->faker->text(250),
            'file1' => 'data:application/pdf;base64,' . $this->faker->text(100),
            'file2' => 'data:application/pdf;base64,' . $this->faker->text(100),
            'file3' => 'data:application/pdf;base64,' . $this->faker->text(100),
            "filename1:en" => $this->faker->text(255),
            "filename1:uk" => $this->faker->text(255),
            "filename2:en" => $this->faker->text(255),
            "filename2:uk" => $this->faker->text(255),
            "filename3:en" => $this->faker->text(255),
            "filename3:uk" => $this->faker->text(255),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/page/' . $page->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $page->id,
                    'category_id' => $pageCategory->id,
                    'uri' => $page->uri,
                    'file1' => $page->file1,
                    'file2' => $page->file2,
                    'file3' => $page->file3,
                    'translations' => [
                        [
                            'locale' => 'en',
                            'page_id' => $page->id,
                            'name' => $data['name:en'],
                            'text' => $data['text:en'],
                            'meta_title' => $data['meta_title:en'],
                            'meta_description' => $data['meta_description:en'],
                            'filename1' => $data['filename1:en'],
                            'filename2' => $data['filename2:en'],
                            'filename3' => $data['filename3:en'],
                        ],
                        [
                            'locale' => 'uk',
                            'page_id' => $page->id,
                            'name' => $data['name:uk'],
                            'text' => $data['text:uk'],
                            'meta_title' => $data['meta_title:uk'],
                            'meta_description' => $data['meta_description:uk'],
                            'filename1' => $data['filename1:uk'],
                            'filename2' => $data['filename2:uk'],
                            'filename3' => $data['filename3:uk'],
                        ]
                    ]
                ]
            );

        foreach ($page->translations as $translation) {
            $this->assertDatabaseHas('page_translations', [
                'id' => $translation->id,
                'locale' => $translation->locale,
                'name' => $data['name:' . $translation->locale],
                'text' => $data['text:' . $translation->locale],
                'meta_title' => $data['meta_title:' . $translation->locale],
                'meta_description' => $data['meta_description:' . $translation->locale],
                'page_id' => $page->id,
                'filename1' => $data['filename1:' . $translation->locale],
                'filename2' => $data['filename2:' . $translation->locale],
                'filename3' => $data['filename3:' . $translation->locale],
            ]);
        }
    }

    public function testShowForMissingData()
    {
        Page::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/page/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Page::factory()->create();

        $data = [
            'name:en' => $this->faker->text(15),
            'name:uk' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/page/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Page::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/page/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
