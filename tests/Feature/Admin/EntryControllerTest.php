<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EntryControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $service = Service::factory()->create();
        $entries = Entry::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $this->actingAs($this->user)
            ->json('get', 'api/v1/entries')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'stat' => [
                    'today' => $entries->count(),
                    'yesterday' => 0,
                    'week' => $entries->count(),
                    'month' => $entries->count(),
                    'year' => $entries->count()
                ],
                'data' => $entries->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'user_id' => $item->user_id,
                        'service_id' => $item->service_id,
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        $service = Service::factory()->create();

        $data = [
            'status' => 'active',
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/entry', $data);

        $entry = Entry::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $entry->id,
                'user_id' => $entry->user_id,
                'service_id' => $entry->service_id,
            ]);

        $this->assertDatabaseHas('entries', [
            'id' => $entry->id,
            'user_id' => $entry->user_id,
            'service_id' => $entry->service_id,
        ]);
    }

    public function testShowAction()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/entry/' . $entry->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $entry->id,
                'user_id' => $entry->user_id,
                'service_id' => $entry->service_id,
            ]);
    }

    public function testUpdateAction()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $data = [
            'status' => 'active',
        ];

        $this->actingAs($this->user)
            ->put('api/v1/entry/' . $entry->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'id' => $entry->id,
                    'user_id' => $entry->user_id,
                    'service_id' => $entry->service_id,
                ]
            );
    }

    public function testAddFileToEntryFilesTable()
    {
        Storage::fake('public');

        $service = Service::factory()->create();
        $time = Carbon::now();

        $data = [
            'user_id' => $this->user->id,
            'service_id' => $service->id,
            'files' => [
                '0' => $name = UploadedFile::fake()->image('test1.jpg'),
                '1' => UploadedFile::fake()->image('test2.jpg')
            ]
        ];
        $response = $this->actingAs($this->user)
            ->postJson('api/v1/entry', $data);

        $entry = Entry::first();

        $response->assertStatus(Response::HTTP_OK);

        Storage::disk('public')->assertExists("entries/{$time->format('Y')}/{$time->format('m')}/{$time->format('d')}/" . $name->hashName());

        $this->assertDatabaseHas('entries', [
            'user_id' => $data['user_id'],
            'service_id' => $data['service_id'],
        ]);
        $this->assertDatabaseHas('entry_files', [
            'entry_id' => $entry->id
        ]);
    }

    public function testAddNotArrayFiles()
    {
        Storage::fake('public');

        $service = Service::factory()->create();

        $data = [
            'user_id' => $this->user->id,
            'service_id' => $service->id,
            'files' => UploadedFile::fake()->image('test1.jpg')
        ];

        $this->actingAs($this->user)
            ->json('post', 'api/v1/entry', $data)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testAddFileWithNotValidMimes()
    {
        Storage::fake('public');

        $service = Service::factory()->create();

        $data = [
            'user_id' => $this->user->id,
            'service_id' => $service->id,
            'files' => [
                '0' => $name = UploadedFile::fake()->image('test1.exe'),
                '1' => UploadedFile::fake()->image('test2.exe')
            ]
        ];
        $response = $this->actingAs($this->user)
            ->postJson('api/v1/entry', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShowForMissingData()
    {
        $service = Service::factory()->create();
        Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $this->actingAs($this->user)
            ->get('api/v1/entry/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        $service = Service::factory()->create();
        Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $data = [
            'status' => 'active',
        ];

        $this->actingAs($this->user)
            ->put('api/v1/entry/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
