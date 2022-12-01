<?php

namespace Tests\Feature\Admin;

use App\Models\LawCategory;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class LawCategoryControllerTest extends TestCase
{
    public function testIndexAction()
    {
        LawCategory::factory()->create();
        $this->actingAs($this->user)
            ->json('get', 'api/v1/law_categories')
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

    public function testCreateAction()
    {
        $parent = LawCategory::factory()->create();
        $data = [
            'parent_id' => (string)$parent->id,
            'status' => 1,
            'name:uk' => "Viktor",
            'name:en' => "Igor",
        ];
        $this->actingAs($this->user)
            ->json('post', 'api/v1/law_category', $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "published_at",
                    "id",
                    "name",
                    "translations" =>
                    [
                        [
                            "locale",
                            "name",
                            "law_category_id",
                            "id"
                        ],
                        [
                            "locale",
                            "name",
                            "law_category_id",
                            "id"
                        ],
                    ]
                ]
            );
        $this->assertDatabaseHas('law_categories', ['published_at' => Carbon::now()->toDateTimeString(), 'parent_id' => $parent->id]);
        $this->assertDatabaseHas('law_category_translations', ['name' => $data['name:en']]);
        $this->assertDatabaseHas('law_category_translations', ['name' => $data['name:uk']]);
    }

    public function testShowAction()
    {

        $lawCategory = LawCategory::factory()->create([
            'name:en' => $name_en = "Viktor",
            'name:uk' => $name_uk = "Oleg"
        ]);

        $this->actingAs($this->user)
            ->json('get', "api/v1/law_category/$lawCategory->id")
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

    public function testUpdateAction()
    {
        $lawCategory = LawCategory::factory()->create();

        $data = [
            'status' => 0,
            'name:en' => "Vasya",
            'name:uk' => "Nina",
        ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/law_category/$lawCategory->id", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $lawCategory->id,
                    "parent_id" => null,
                    "published_at" => null,
                    "translations" => [
                        [
                            "law_category_id" => $lawCategory->id,
                            "name" => $data['name:en'],
                        ],
                        [
                            "law_category_id" => $lawCategory->id,
                            "name" => $data['name:uk'],
                        ]
                    ]
                ]
            );
    }

    public function testDeleteAction()
    {

        $lawCategory = LawCategory::factory()->create();

        $this->actingAs($this->user)
            ->json('delete', "api/v1/law_category/$lawCategory->id")
            ->assertStatus(Response::HTTP_OK);
        $this->assertSoftDeleted($lawCategory);
    }

    public function testShowForMissingData()
    {
        LawCategory::factory()->create();
        $this->actingAs($this->user)
            ->json('get', "api/v1/law_category/0")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        LawCategory::factory()->create();
        $data = [
            'status' => 0,
            'name:en' => "Vasya",
            'name:uk' => "Nina",
        ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/law_category/0", $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        LawCategory::factory()->create();
        $this->actingAs($this->user)
            ->json('delete', 'api/v1/law_category/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
