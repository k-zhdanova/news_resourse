<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    public function testLanguagesPage()
    {
        $page = Page::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/pages?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($page->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $pageCategory = PageCategory::factory()->create();
        $pages = Page::factory()->count(10)->create([
            'category_id' => $pageCategory->id,
        ]);

        $response = $this->get('api/v1/web/pages');

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
    public function testShowAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $page = Page::factory()->create([
            'category_id' => $pageCategory->id,
        ]);

        $response = $this->get('api/v1/web/page/' . $page->id);

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

    public function testShowPreviewAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $page = Page::factory()->create([
            'category_id' => $pageCategory->id,
        ]);

        $response = $this->get('api/v1/web/page/preview/' . $page->id);

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

    public function testShowByUriAction()
    {
        $pageCategory = PageCategory::factory()->create();

        $page = Page::factory()->create([
            'category_id' => $pageCategory->id
        ]);

        $response = $this->get('api/v1/web/page/' . $page->uri . '?uri=true');

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
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'page_id' => $translation->page_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Page::factory()->create();

        $this->get('api/v1/web/page/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowByUriForMissingData()
    {
        Page::factory()->create();

        $this->get('api/v1/web/page/?uri=true')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
