<?php

namespace Tests\Feature;

use App\Models\Institution;
use Illuminate\Http\Response;
use Tests\TestCase;

class InstitutionControllerTest extends TestCase
{
    public function testLanguages()
    {
        $institution = Institution::factory()->create();

        $langs = ['en', 'uk'];

        foreach ($langs as $lang) {
            $response = $this->json('get', "api/v1/web/institutions?lang", ['lang' => $lang])
                ->assertStatus(Response::HTTP_OK);
            $this->assertEquals($institution->translate($lang)->name, $response->json('data.0.name'));
        }
    }

    public function testIndexAction()
    {
        $institutions = Institution::factory()->count(4)->create();

        $response = $this->get('api/v1/web/institutions');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $institutions->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uri' => $item->uri,
                        'emails' => $item->emails,
                        'phones' => $item->phones,
                        'website' => $item->website,
                        'translations' => $item->translations->map(function ($translation) {
                            return [
                                'id' => $translation->id,
                                'locale' => $translation->locale,
                                'name' => $translation->name,
                                'meta_title' => $translation->meta_title,
                                'meta_description' => $translation->meta_description,
                                'institution_id' => $translation->institution_id,
                                'address' => $translation->address,
                                'schedule' => $translation->schedule,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
    }

    public function testShowByUriAction()
    {
        $institution = Institution::factory()->create();

        $response = $this->get('api/v1/web/institution/' . $institution->uri);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $institution->id,
                'uri' => $institution->uri,
                'translations' => $institution->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'name' => $translation->name,
                        'meta_title' => $translation->meta_title,
                        'meta_description' => $translation->meta_description,
                        'institution_id' => $translation->institution_id,
                        'address' => $translation->address,
                        'schedule' => $translation->schedule,
                    ];
                })->toArray()
            ]);
    }

    public function testShowForMissingData()
    {
        Institution::factory()->create();

        $this->get('api/v1/web/institution/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowByUriForMissingData()
    {
        Institution::factory()->create();

        $this->get('api/v1/web/institution/')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
