<?php

namespace App\Http\Controllers;

use App\Http\Requests\LawCategoryRequest;
use App\Models\LawCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Category of Laws
 *
 * API для работы с категориями законов.
 */
class LawCategoryController extends Controller
{

    /**
     * List of law category
     *
     * @param LawCategoryRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */

    public function index(LawCategoryRequest $request)
    {
        $linkCategory = new LawCategory();
        $list = $linkCategory->getAll($request);

        return response()->json($list);
    }

    /**
     * Create law category
     *
     * @authenticated
     * @param LawCategoryRequest $request
     *
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из таблицы law_categories
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     */
    public function create(LawCategoryRequest $request)
    {
        $lawCategory = new LawCategory($request->all());

        if ($request->status == 1) {
            $lawCategory->published_at = Carbon::now()->toDateTimeString();
        } else {
            $lawCategory->published_at = NULL;
        }

        if ($request->parent_id) {
            $lawCategory->parent_id = LawCategory::findOrCreateNewInstance($request->parent_id);
        }

        $lawCategory->save();

        return response()->json($lawCategory);
    }

    /**
     * Get specified link category
     *
     * @param LawCategoryRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */
    public function show(LawCategoryRequest $request, $id)
    {
        try {
            $lawCategory = LawCategory::with(['parent'])->with(['childs' => function ($q) {
                $q->published();
            }])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($lawCategory);
    }

    /**
     * Update specified law category
     *
     * @authenticated
     * @param LawCategoryRequest $request
     * @param $id
     * @return JsonResponse
     * @bodyParam name:en string optional Название на английском
     * @bodyParam name:uk string optional Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из тиблици law_categories
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     */
    public function update(LawCategoryRequest $request, $id)
    {
        try {
            $lawCategory = LawCategory::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $lawCategory->fill($request->all());

        if ($request->status == 1 && !$lawCategory->published_at) {
            $lawCategory->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $lawCategory->published_at) {
            $lawCategory->published_at = null;
        }

        if ($request->parent_id) {
            $lawCategory->parent_id = LawCategory::findOrCreateNewInstance($request->parent_id);
        }

        $lawCategory->save();

        return response()->json($lawCategory);
    }

    /**
     * Delete specified law category
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $lawCategory = LawCategory::withCount('laws', 'childs')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($lawCategory->laws_count || $lawCategory->childs_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $lawCategory->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
