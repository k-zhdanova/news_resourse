<?php

namespace Tests\Feature;

use App\Models\Report;
use Illuminate\Http\Response;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $reports = Report::factory()->count(10)->create();

        $response = $this->get('api/v1/web/reports');

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

    public function testShowAction()
    {
        $report = Report::factory()->create();

        $response = $this->get('api/v1/web/report/' . $report->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $report->id,
                'year' => $report->year,
                'month' => $report->month,
                'filename' => $report->filename,
            ]);
    }

    public function testShowForMissingData()
    {
        Report::factory()->create();

        $this->get('api/v1/web/report/0')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
