<?php

namespace Tests\Feature;

use App\Models\LawCategory;
use Illuminate\Http\Response;
use Tests\TestCase;

class LawCategoryControllerTest extends TestCase
{
    public function testIndexAction()
    {
        LawCategory::factory()->create();
        $this->json('get', 'api/v1/web/law_categories')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data" => [
                        '*' => [
                            "id",
                            "parent_id",
                            "published_at",
                            "created_at",
                            "updated_at",
                            "name",
                            "translations"
                        ]
                    ]

                ]
            );
    }

    public function testShowAction()
    {
        $lawCategory = LawCategory::factory()->create([
            'name:en' => $name_en = "Viktor",
            'name:uk' => $name_uk = "Oleg"
        ]);

        $this->json('get', "api/v1/web/law_category/$lawCategory->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $lawCategory->id,
                    "parent_id" => null,
                    "published_at" => $lawCategory->published_at,
                    "translations" => [
                        [
                            "law_category_id" => $lawCategory->id,
                            "name" => $name_en,
                        ],
                        [
                            "law_category_id" => $lawCategory->id,
                            "name" => $name_uk,
                        ]
                    ]
                ]
            );
    }

    public function testShowForMissingData()
    {
        LawCategory::factory()->create();
        $this->json('get', "api/v1/web/law_category/0")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
