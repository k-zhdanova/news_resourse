<?php

namespace Tests\Feature\Admin;

use App\Models\Report;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $reports = Report::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/reports');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $reports->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'month' => $item->month,
                        'filename' => $item->filename,
                    ];
                })->toArray()
            ]);
    }

    public function testCreateAction()
    {
        Storage::fake('public');
        $data = [
            "year" => $this->faker->year(),
            "month" => $this->faker->month(),
            "file" => 'data:application/pdf;base64,' . $this->faker->text(100)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('api/v1/report', $data);

        $report = Report::first();
        $report->month = strlen($report->month) == 1 ? 0 . $report->month : $report->month;

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $report->id,
                'year' => $report->year,
                'month' => $report->month,
                'filename' => $report->filename,
            ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'year' => $report->year,
            'month' => $report->month,
            'filename' => $report->filename,
        ]);

        Storage::disk('public')->assertExists($report->filename);
    }

    public function testShowAction()
    {
        $report = Report::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('api/v1/report/' . $report->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $report->id,
                'year' => $report->year,
                'month' => $report->month,
                'filename' => $report->filename,
            ]);
    }

    public function testDeleteAction()
    {
        Storage::fake('public');
        $report = Report::factory()->create();

        $this->actingAs($this->user)
            ->put('api/v1/report/' . $report->id, ["file" => $this->faker->text(100)]);

        Storage::disk('public')->assertExists($report->filename);

        $this->actingAs($this->user)
            ->delete('api/v1/report/' . $report->id)
            ->assertStatus(Response::HTTP_OK);

        Storage::disk('public')->assertMissing($report->filename);
        $this->assertSoftDeleted($report);
    }

    public function testUpdateAction()
    {
        $report = Report::factory()->create();

        $data = [
            "year" => $this->faker->year(),
            "month" => $this->faker->month(),
            "file" => 'data:application/pdf;base64,' . $this->faker->text(100)
        ];

        $this->actingAs($this->user)
            ->put('api/v1/report/' . $report->id, $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $report->id,
                'year' => $data['year'],
                'month' => $data['month'],
            ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'year' => $data['year'],
            'month' => $data['month'],
        ]);

        $updatedReport = Report::first();

        Storage::disk('public')->assertExists($updatedReport->filename);
    }

    public function testShowForMissingData()
    {
        Report::factory()->create();

        $this->actingAs($this->user)
            ->get('api/v1/report/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateForMissingData()
    {
        Report::factory()->create();

        $data = [
            "year" => $this->faker->year(),
            "month" => $this->faker->month()
        ];

        $this->actingAs($this->user)
            ->put('api/v1/report/0', $data)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteForMissingData()
    {
        Report::factory()->create();

        $this->actingAs($this->user)
            ->delete('api/v1/report/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
