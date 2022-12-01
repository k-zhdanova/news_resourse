<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * @group Reports
 *
 * API для работы с отчетами.
 */
class ReportController extends Controller
{
    /**
     * List of reports
     *
     * @param ReportRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @queryParam full boolean Получение списка без пагинации
     */

    public function index(ReportRequest $request)
    {
        $report = new Report();
        $list = $report->getAll($request);
        return response()->json($list);
    }

    /**
     * Create report
     *
     * @authenticated
     * @param ReportRequest $request
     *
     * @return JsonResponse
     * @bodyParam year integer required год отчета
     * @bodyParam month integer required месяц отчета
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam file optional Файл к отчету
     */
    public function create(ReportRequest $request)
    {
        $report = new Report($request->all());
        if ($request->status == 1) {
            $report->published_at = Carbon::now()->toDateTimeString();
        } else {
            $report->published_at = NULL;
        }

        if ($request->file) {
            $report->filename = $report->saveFile($request->file);
        }

        $report->save();

        return response()->json($report);
    }

    /**
     * Get specified report
     *
     * @param ReportRequest $request
     * @param string $id
     *
     * @return JsonResponse
     */
    public function show(ReportRequest $request, $id)
    {
        try {
            $report = Report::findOrFail($id);
            if ($report->month) {
                $report->month = strlen($report->month) == 1 ? 0 . $report->month : $report->month;
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($report);
    }

    /**
     * Update specified report
     *
     * @authenticated
     * @param ReportRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @bodyParam year integer optional год отчета
     * @bodyParam month integer optional месяц отчета
     * @bodyParam status integer optional Статус отображения (1-отображается, 0-скрыто)
     */
    public function update(ReportRequest $request, $id)
    {
        try {
            $report = Report::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $report->fill($request->all());

        if ($request->status == 1 && !$report->published_at) {
            $report->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $report->published_at) {
            $report->published_at = null;
        }

        if ($request->file) {
            $old_filename = $report->filename;
            $report->filename = $report->saveFile($request->file);

            if ($report->isDirty('filename') && $old_filename != NULL) {
                Storage::disk('public')->delete($old_filename);
            }
        }

        $report->save();

        return response()->json($report);
    }

    /**
     * Delete specified report
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $report = Report::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        Storage::disk('public')->delete($report->filename);
        $report->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
