<?php

namespace Tests\Feature;

use App\Models\Queue;
use Illuminate\Http\Response;
use Tests\TestCase;

class QueueControllerTest1 extends TestCase
{
    public function testIndexAction()
    {
        $queues = Queue::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/web/queues');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $queues->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'status' => $item->status,
                        'is_cron' => $item->is_cron,
                        'slot_duration' => $item->slot_duration,
                        'mon' => $item->mon,
                        'tue' => $item->tue,
                        'wed' => $item->wed,
                        'thu' => $item->thu,
                        'fri' => $item->fri,
                        'sat' => $item->sat,
                        'sun' => $item->sun,
                        'break' => $item->break,
                    ];
                })->toArray()
            ]);
    }

    public function testShowAction()
    {
        $item = Queue::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/web/queue/' . $item->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $item->id,
                'name' => $item->name,
                'status' => $item->status,
                'is_cron' => $item->is_cron,
                'slot_duration' => $item->slot_duration,
                'mon' => $item->mon,
                'tue' => $item->tue,
                'wed' => $item->wed,
                'thu' => $item->thu,
                'fri' => $item->fri,
                'sat' => $item->sat,
                'sun' => $item->sun,
                'break' => $item->break,
            ]);
    }

    public function testShowForMissingData()
    {
        Queue::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/web/queue/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
