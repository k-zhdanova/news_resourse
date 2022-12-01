<?php

namespace Tests\Feature\Cabinet;

use App\Models\Service;
use App\Models\Entry;
use App\Models\EntryReview;
use Illuminate\Http\Response;
use Tests\TestCase;

class EntryReviewControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $entryReviews = EntryReview::factory()->count(10)->create([
            'entry_id' => $entry->id
        ]);

        $this->actingAs($this->user)
            ->json('get', 'api/v1/cabinet/entry_reviews')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                $entryReviews->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'user_id' => $item->user_id,
                        'entry_id' => $item->entry_id,
                        'text' => $item->text,
                    ];
                })->toArray()
            );
    }


    public function testCreateAction()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $data = [
            'text' => $this->faker->text(20),
            'entry_id' => $entry->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/cabinet/entry_review', $data);

        $entryReview = EntryReview::first();

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $entryReview->id,
                'user_id' => $entryReview->user_id,
                'entry_id' => $entryReview->entry_id,
                'text' => $entryReview->text,
            ]);

        $this->assertDatabaseHas('entry_reviews', [
            'id' => $entryReview->id,
            'user_id' => $entryReview->user_id,
            'entry_id' => $entryReview->entry_id,
            'text' => $entryReview->text,
        ]);
    }

    public function testShowAction()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        $entryReview = EntryReview::factory()->create([
            'entry_id' => $entry->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('api/v1/cabinet/entry_review/' . $entry->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $entryReview->id,
                'user_id' => $entryReview->user_id,
                'entry_id' => $entryReview->entry_id,
                'text' => $entryReview->text,
            ]);
    }

    public function testShowForMissingData()
    {
        $service = Service::factory()->create();
        $entry = Entry::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
        ]);

        EntryReview::factory()->create([
            'entry_id' => $entry->id
        ]);

        $this->actingAs($this->user)
            ->get('api/v1/cabinet/entry_review/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
