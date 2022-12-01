<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerateSitemapTest extends TestCase
{
    private string $sitemap_path;

    public function init()
    {
        $this->sitemap_path = public_path() . '/sitemap.xml';

        if (File::exists($this->sitemap_path)) {
            File::delete($this->sitemap_path);
        }
    }

    public function testEmptySitemapGenerate()
    {
        $this->init();

        Artisan::call('generate:sitemap');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
</urlset>';

        $this->assertXmlStringEqualsXmlFile($this->sitemap_path, $xml);
    }

    public function testSitemapGenerate()
    {
        $this->init();

        $newsCategory = NewsCategory::factory()->create();
        $news = News::factory()->create([
            'category_id' => $newsCategory->id,
        ]);
        $news->is_published = 1;
        $news->save();

        Artisan::call('generate:sitemap');

        $xmlString = file_get_contents($this->sitemap_path);
        $host = request()->getSchemeAndHttpHost();
        $url = "{$host}/news/{$news->uri}";
        $tag = "<loc>{$url}</loc>";
        $this->assertStringContainsString($tag, $xmlString);

        $tag = "<xhtml:link rel=\"alternate\" hreflang=\"uk-UA\" href=\"{$url}\" />";
        $this->assertStringContainsString($tag, $xmlString);

        $url = "{$host}/en/news/{$news->uri}";
        $tag = "<xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$url}\" />";
        $this->assertStringContainsString($tag, $xmlString);
    }

}
