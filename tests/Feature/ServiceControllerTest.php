<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\Service;
use Illuminate\Http\Response;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    public function testLanguagesNews()
    {
        $service = Service::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/services?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($service->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();
        $services = Service::factory()->count(10)->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->get('api/v1/web/services');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $services->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sector_id' => $item->sector_id,
                        'category_id' => $item->category_id,
                        'institution_id' => $item->institution_id,
                        'uri' => $item->uri,
                        'is_online' => $item->is_online,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'text' => $translation->text,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'service_id' => $translation->service_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $service = Service::factory()->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->get('api/v1/web/service/' . $service->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $service->id,
                'sector_id' => $service->sector_id,
                'category_id' => $service->category_id,
                'institution_id' => $service->institution_id,
                'uri' => $service->uri,
                'is_online' => $service->is_online,
                'translations' => $service->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'service_id' => $translation->service_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowPreviewAction3()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $service = Service::factory()->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->get('api/v1/web/service/preview/' . $service->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $service->id,
                'sector_id' => $service->sector_id,
                'category_id' => $service->category_id,
                'institution_id' => $service->institution_id,
                'uri' => $service->uri,
                'is_online' => $service->is_online,
                'translations' => $service->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'service_id' => $translation->service_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowByUriAction()
    {
        $sector = Sector::factory()->create();
        $category = Category::factory()->create();
        $institution = Institution::factory()->create();

        $service = Service::factory()->create([
            'sector_id' => $sector->id,
            'category_id' => $category->id,
            'institution_id' => $institution->id,
        ]);

        $response = $this->get('api/v1/web/service/' . $service->uri . '?uri=true');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $service->id,
                'sector_id' => $service->sector_id,
                'category_id' => $service->category_id,
                'institution_id' => $service->institution_id,
                'uri' => $service->uri,
                'is_online' => $service->is_online,
                'translations' => $service->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'text' => $translation->text,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'service_id' => $translation->service_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Service::factory()->create();

        $this->get('api/v1/web/service/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowByUriForMissingData()
    {
        Service::factory()->create();

        $this->get('api/v1/web/service/?uri=true')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
