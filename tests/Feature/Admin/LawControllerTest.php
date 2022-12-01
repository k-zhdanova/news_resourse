<?php

namespace Tests\Feature\Admin;

use App\Models\Law;
use App\Models\LawCategory;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class LawControllerTest extends TestCase
{
    public function testIndexAction()
    {

        LawCategory::factory()->create();
        Law::factory()->create();
        $this->actingAs($this->user)
            ->json('get', 'api/v1/laws')
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


    public function testCreateAction()
    {
        LawCategory::factory()->create();
        $data = [
            'status' => 1,
            'category_id' => '1',
            'name:uk' => "Viktor",
            'name:en' => "Igor",
            'link' => "https://laravel.su/",
        ];
        $this->actingAs($this->user)
            ->json('post', 'api/v1/law', $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    "link",
                    "category_id",
                    "published_at",
                    "id",
                    "name",
                    "translations" =>
                    [
                        [
                            "locale",
                            "name",
                            "law_id",
                            "id"
                        ],
                        [
                            "locale",
                            "name",
                            "law_id",
                            "id"
                        ],
                    ]
                ]
            );
        $this->assertDatabaseHas('laws', ['category_id' => $data['category_id'], 'link' => $data['link']]);
        $this->assertDatabaseHas('law_translations', ['name' => $data['name:en']]);
        $this->assertDatabaseHas('law_translations', ['name' => $data['name:uk']]);
    }

    public function testShowAction()
    {
        LawCategory::factory()->create();
        $item = Law::factory()->create([
            'name:en' => $name_en = "Viktor",
            'name:uk' => $name_uk = "Oleg"
        ]);

        $this->actingAs($this->user)
            ->json('get', "api/v1/law/$item->id")
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

    public function testUpdateAction()
    {
        LawCategory::factory()->create();
        $item = Law::factory()->create();

        $data = [
            'status' => 0,
            'name:en' => "Vasya",
            'name:uk' => "Nina",
        ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/law/$item->id", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    "id" => $item->id,
                    "published_at" => null,
                    "translations" => [
                        [
                            "law_id" => $item->id,
                            "name" => $data['name:en'],
                        ],
                        [
                            "law_id" => $item->id,
                            "name" => $data['name:uk'],
                        ]
                    ]
                ]
            );
    }

    public function testDeleteAction()
    {
        LawCategory::factory()->create();

        $item = Law::factory()->create();

        $this->actingAs($this->user)
            ->json('delete', "api/v1/law/$item->id")
            ->assertStatus(Response::HTTP_OK);
        $this->assertSoftDeleted($item);
    }

    public function testShowForMissingData()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $this->actingAs($this->user)
            ->json('get', "api/v1/law/0")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $data = [
            'status' => 0,
            'name:en' => "Vasya",
            'name:uk' => "Nina",
        ];

        $this->actingAs($this->user)
            ->json('put', "/api/v1/law/0", $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        LawCategory::factory()->create();
        Law::factory()->create();
        $this->actingAs($this->user)
            ->json('delete', 'api/v1/law/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
