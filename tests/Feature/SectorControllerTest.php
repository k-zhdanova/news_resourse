<?php

namespace Tests\Feature;

use App\Models\Sector;
use Illuminate\Http\Response;
use Tests\TestCase;

class SectorControllerTest extends TestCase
{
    public function testLanguagesNews()
    {
        $sector = Sector::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/sectors?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($sector->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $sectors = Sector::factory()->count(10)->create();

        $response = $this->get('api/v1/web/sectors');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $sectors->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uri' => $item->uri,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'sector_id' => $translation->sector_id,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $sector = Sector::factory()->create();

        $response = $this->get('api/v1/web/sector/' . $sector->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $sector->id,
                'uri' => $sector->uri,
                'translations' => $sector->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'sector_id' => $translation->sector_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowByUriAction()
    {
        $sector = Sector::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/sector/' . $sector->uri . '?uri=true');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $sector->id,
                'uri' => $sector->uri,
                'translations' => $sector->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'sector_id' => $translation->sector_id,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Sector::factory()->create();

        $this->get('api/v1/web/sector/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowByUriForMissingData()
    {
        Sector::factory()->create();

        $this->get('api/v1/web/sector/?uri=true')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
