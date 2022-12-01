<?php

namespace Tests\Feature;

use App\Models\Law;
use App\Models\LawCategory;
use App\Models\LawTranslation;
use Illuminate\Http\Response;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function testSearchedData()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $item = LawTranslation::first();
        $parent_item = Law::first();

        $response = $this->json('get', "api/v1/web/search?q={$item->name}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => 1,
            'name' => $item->name,
            'model' => 'law',
            'locale' => $item->locale,
            'uri' => $parent_item->link,
            'tags' => []
        ]);
        $response->assertJsonFragment([
            'current_page' => 1,
        ]);
    }

    public function testEmptyData()
    {
        $this->json('get', 'api/v1/web/search?q=123')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                []
            );
    }

    public function testInvalidData()
    {
        $this->json('get', 'api/v1/web/search')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "q" => [
                          0 => "Поле q є обов'язковим для заповнення."
                        ]
                    ]
                ]
            );

        $this->json('get', 'api/v1/web/search?q=12')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "q" => [
                            0 => "Текст у полі q повинен містити не менше 3 символів."
                        ]
                    ]
                ]
            );

        $this->json('get', 'api/v1/web/search?q=123&lang=fr')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "lang" => [
                            0 => "Вибране для lang значення не коректне."
                        ]
                    ]
                ]
            );
    }

}
