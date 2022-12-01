<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkRequest;
use App\Models\Link;
use App\Models\LinkCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Links
 *
 * API для работы с ссылками.
 */
class LinkController extends Controller
{
    /**
     * List of link
     *
     * @param LinkRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */

    public function index(LinkRequest $request)
    {
        $link = new Link();
        $list = $link->getAll($request);
        return response()->json($list);
    }

    /**
     * Create link
     *
     * @authenticated
     * @param LinkRequest $request
     *
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam link url required Ссылка
     * @bodyParam category_id integer required ID Категории ссылки
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam follow integer  Статус отслеживания (1-да, 0-нет)
     */
    public function create(LinkRequest $request)
    {
        $link = new Link($request->all());

        if ($request->status == 1) {
            $link->published_at = Carbon::now()->toDateTimeString();
        } else {
            $link->published_at = NULL;
        }

        if ($request->category_id) {
            $link->category_id = LinkCategory::findOrCreateNewInstance($request->category_id);
        }

        $link->save();

        return response()->json($link);
    }

    /**
     * Get specified link
     *
     * @param LinkRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */
    public function show(LinkRequest $request, $id)
    {
        try {
            $link = Link::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($link);
    }

    /**
     * Update specified link category
     *
     * @authenticated
     * @param LinkRequest $request
     * @param $id
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam link url required Ссылка
     * @bodyParam category_id integer required ID Категории ссылки
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam follow integer  Статус отслеживания (1-да, 0-нет)
     */
    public function update(LinkRequest $request, $id)
    {
        try {
            $link = Link::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $link->fill($request->all());


        if ($request->status == 1 && !$link->published_at) {
            $link->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $link->published_at) {
            $link->published_at = null;
        }

        if ($request->category_id) {
            $link->category_id = LinkCategory::findOrCreateNewInstance($request->category_id);
        }

        $link->save();


        return response()->json($link);
    }

    /**
     * Delete specified link category
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $link = Link::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $link->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
