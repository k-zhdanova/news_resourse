<?php

namespace Tests\Feature\Admin;

use App\Models\Queue;
use Database\Factories\QueueFactory;
use Illuminate\Http\Response;
use Tests\TestCase;

class QueueControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $queues = Queue::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/queues');

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

    public function testCreateAction()
    {

        $data = [
            'name' => $this->faker->text(15),
            'status' => $this->faker->numberBetween(0, 1),
            'is_cron' => $this->faker->numberBetween(0, 1),
            'slot_duration' => $this->faker->numberBetween(30, 60),
            'mon' => QueueFactory::DEFAULT_WORK_TIME,
            'tue' => QueueFactory::DEFAULT_WORK_TIME,
            'wed' => QueueFactory::DEFAULT_WORK_TIME,
            'thu' => QueueFactory::DEFAULT_WORK_TIME,
            'fri' => QueueFactory::DEFAULT_WORK_TIME,
            'sat' => QueueFactory::DEFAULT_WORK_TIME,
            'sun' => QueueFactory::DEFAULT_WORK_TIME,
            'break' => QueueFactory::DEFAULT_BREAK_TIME,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/queue', $data);

        $item = Queue::first();

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

        $this->assertDatabaseHas('queues', [
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

    public function testShowAction()
    {
        $item = Queue::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/queue/' . $item->id);

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

    public function testDeleteAction()
    {
        $queue = Queue::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/queue/' . $queue->id)
            ->assertStatus(Response::HTTP_OK);

        $this->assertSoftDeleted($queue);
    }

    public function testUpdateAction()
    {
        $item = Queue::factory()->create();

        $data = [
            'name' => $this->faker->text(15),
            'status' => $this->faker->numberBetween(0, 1),
            'is_cron' => $this->faker->numberBetween(0, 1),
            'slot_duration' => $this->faker->numberBetween(30, 60),
            'mon' => QueueFactory::DEFAULT_WORK_TIME,
            'tue' => QueueFactory::DEFAULT_WORK_TIME,
            'wed' => QueueFactory::DEFAULT_WORK_TIME,
            'thu' => QueueFactory::DEFAULT_WORK_TIME,
            'fri' => QueueFactory::DEFAULT_WORK_TIME,
            'sat' => QueueFactory::DEFAULT_WORK_TIME,
            'sun' => QueueFactory::DEFAULT_WORK_TIME,
            'break' => QueueFactory::DEFAULT_BREAK_TIME,
        ];

        $response = $this->actingAs($this->user)
            ->put('api/v1/queue/' . $item->id, $data);

        $item->refresh();

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
            ->get('api/v1/queue/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Queue::factory()->create();

        $data = [
            'name' => $this->faker->text(15),
        ];

        $this->actingAs($this->user)
            ->put('api/v1/queue/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Queue::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/queue/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
