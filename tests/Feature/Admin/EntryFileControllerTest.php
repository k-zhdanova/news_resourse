<?php

namespace Tests\Feature\Admin;

use App\Models\Entry;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EntryFileControllerTest extends TestCase
{
    public function testCreateAction()
    {
        Storage::fake('public');

        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $data = [
            'entry_id' => $entry->id,
            'files' => [
                '0' => $name = UploadedFile::fake()->image('test1.jpg'),
                '1' => UploadedFile::fake()->image('test2.jpg')
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/file', $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    '*' => [
                        "id",
                        "entry_id",
                        "filename",
                        "created_at"
                    ]
                ]
            );

        Storage::disk('public')->assertExists("{$response[0]['filename']}");
        $this->assertDatabaseHas('entry_files', ['entry_id' => $data['entry_id']]);
    }
}
