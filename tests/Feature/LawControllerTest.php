<?php

namespace Tests\Feature;

use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class LawControllerTest extends TestCase
{
    public function testIndexAction()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $this->json('get', 'api/v1/web/laws')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data" => [
                        '*' => [
                            "id",
                            "category_id",
                            "link",
                            "published_at",
                            "follow",
                            "created_at",
                            "updated_at",
                            "name",
                            "category",
                            "translations",
                        ]
                    ]
                ]
            );
    }

    public function testShowAction()
    {
        LawCategory::factory()->create();
        $item = Law::factory()->create([
            'name:en' => $name_en = "Viktor",
            'name:uk' => $name_uk = "Oleg"
        ]);

        $this->json('get', "api/v1/web/law/$item->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $item->id,
                    "published_at" => $item->published_at,
                    "translations" => [
                        [
                            "law_id" => $item->id,
                            "name" => $name_en,
                        ],
                        [
                            "law_id" => $item->id,
                            "name" => $name_uk,
                        ]
                    ]
                ]
            );
    }

    public function testShowForMissingData()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $this->json('get', "api/v1/web/law/0")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
