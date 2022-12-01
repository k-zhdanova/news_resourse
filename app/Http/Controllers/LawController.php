<?php

namespace App\Http\Controllers;

use App\Http\Requests\LawRequest;
use App\Models\Law;
use App\Models\LawCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Laws
 *
 * API для работы с законами.
 */
class LawController extends Controller
{
    /**
     * List of laws
     *
     * @param LawRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */

    public function index(LawRequest $request)
    {
        $law = new Law();
        $list = $law->getAll($request);

        return response()->json($list);
    }

    /**
     * Create law
     *
     * @authenticated
     * @param LawRequest $request
     *
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam law url required Ссылка
     * @bodyParam category_id integer required ID Категории закона
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam follow integer  Статус отслеживания (1-да, 0-нет)
     */
    public function create(LawRequest $request)
    {
        $law = new Law($request->all());

        if ($request->status == 1) {
            $law->published_at = Carbon::now()->toDateTimeString();
        } else {
            $law->published_at = NULL;
        }

        if ($request->category_id) {
            $law->category_id = LawCategory::findOrCreateNewInstance($request->category_id);
        }

        $law->save();

        return response()->json($law);
    }

    /**
     * Get specified link
     *
     * @param LawRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */
    public function show(LawRequest $request, $id)
    {
        try {
            $law = Law::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($law);
    }

    /**
     * Update specified law category
     *
     * @authenticated
     * @param LawRequest $request
     * @param $id
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam link url required Ссылка
     * @bodyParam category_id integer required ID Категории закона
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam follow integer  Статус отслеживания (1-да, 0-нет)
     */
    public function update(LawRequest $request, $id)
    {
        try {
            $law = Law::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $law->fill($request->all());

        if ($request->status == 1 && !$law->published_at) {
            $law->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $law->published_at) {
            $law->published_at = null;
        }

        if ($request->category_id) {
            $law->category_id = LawCategory::findOrCreateNewInstance($request->category_id);
        }

        $law->save();

        return response()->json($law);
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
            $law = Law::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $law->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
