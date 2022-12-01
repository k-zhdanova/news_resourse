<?php

namespace Tests\Feature\Admin;

use Carbon\Carbon;
use App\Models\FeedBack;
use App\Models\Service;
use Illuminate\Http\Response;
use Tests\TestCase;

class FeedBackControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $service = Service::factory()->create();
        $feedBacks = FeedBack::factory()->count(10)->create([
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/feedbacks');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'stat' => [
                    'today' => $feedBacks->count(),
                    'yesterday' => 0,
                    'week' => $feedBacks->count(),
                    'month' => $feedBacks->count(),
                    'year' => $feedBacks->count()
                ],
                'data' => $feedBacks->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'text' => $item->text,
                        'email' => $item->email,
                        'status' => $item->status,
                        'date' => $item->date,
                        'age' => $item->age,
                        'sex' => $item->sex,
                        'service_id' => $item->service_id,
                        'is_satisfied' => $item->is_satisfied,
                        'reception_friendly' => $item->reception_friendly,
                        'reception_competent' => $item->reception_competent,
                        'center_friendly' => $item->center_friendly,
                        'center_competent' => $item->center_competent,
                        'website' => $item->website,
                        'impression' => $item->impression
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $service = Service::factory()->create();
        $feedBack = FeedBack::factory()->create([
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/feedback/' . $feedBack->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $feedBack->id,
                'name' => $feedBack->name,
                'text' => $feedBack->text,
                'email' => $feedBack->email,
                'status' => $feedBack->status,
                'date' => date('d.m.Y', strtotime($feedBack->date)),
                'age' => $feedBack->age,
                'sex' => $feedBack->sex,
                'service_id' => $feedBack->service_id,
                'is_satisfied' => $feedBack->is_satisfied,
                'reception_friendly' => $feedBack->reception_friendly,
                'reception_competent' => $feedBack->reception_competent,
                'center_friendly' => $feedBack->center_friendly,
                'center_competent' => $feedBack->center_competent,
                'website' => $feedBack->website,
                'impression' => $feedBack->impression
            ]);
    }

    public function testUpdateAction()
    {
        $service = Service::factory()->create();
        $feedBack = FeedBack::factory()->create([
            'service_id' => $service->id
        ]);

        $data = [
            'name' => $this->faker->name,
            'text' => $this->faker->text(20),
            'email' => $this->faker->email,
            'service_id' => $service->id,
            'status' => $this->faker->numberBetween(1, 3),
            'date' => Carbon::now()->toDateTimeString(),
            'age' => $this->faker->numberBetween(18, 100),
            'sex' => $this->faker->randomElement(['male', 'female']),
            'is_satisfied' => $this->faker->numberBetween(1, 5),
            'reception_friendly' => $this->faker->numberBetween(1, 5),
            'reception_competent' => $this->faker->numberBetween(1, 5),
            'center_friendly' => $this->faker->numberBetween(1, 5),
            'center_competent' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->numberBetween(1, 5),
            'impression' => $this->faker->numberBetween(1, 5)
        ];

        $this->actingAs($this->user)
            ->put('api/v1/feedback/' . $feedBack->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $feedBack->id,
                    'name' => $data['name'],
                    'text' => $data['text'],
                    'email' => $data['email'],
                    'status' => $data['status'],
                    'date' => $data['date'],
                    'age' => $data['age'],
                    'sex' => $data['sex'],
                    'service_id' => $data['service_id'],
                    'is_satisfied' => $data['is_satisfied'],
                    'reception_friendly' => $data['reception_friendly'],
                    'reception_competent' => $data['reception_competent'],
                    'center_friendly' => $data['center_friendly'],
                    'center_competent' => $data['center_competent'],
                    'website' => $data['website'],
                    'impression' => $data['impression']
                ]
            );

        $this->assertDatabaseHas('feed_backs', [
            'id' => $feedBack->id,
            'name' => $data['name'],
            'text' => $data['text'],
            'email' => $data['email'],
            'status' => $data['status'],
            'date' => $data['date'],
            'age' => $data['age'],
            'sex' => $data['sex'],
            'service_id' => $data['service_id'],
            'is_satisfied' => $data['is_satisfied'],
            'reception_friendly' => $data['reception_friendly'],
            'reception_competent' => $data['reception_competent'],
            'center_friendly' => $data['center_friendly'],
            'center_competent' => $data['center_competent'],
            'website' => $data['website'],
            'impression' => $data['impression']
        ]);
    }

    public function testShowForMissingData()
    {
        $service = Service::factory()->create();
        FeedBack::factory()->create([
            'service_id' => $service->id
        ]);


        $this->actingAs($this->user)
            ->get('api/v1/feedback/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        $service = Service::factory()->create();
        FeedBack::factory()->create([
            'service_id' => $service->id
        ]);

        $data = [
            'name' => $this->faker->name,
            'text' => $this->faker->text(20),
            'email' => $this->faker->email,
        ];

        $this->actingAs($this->user)
            ->put('api/v1/feedback/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
