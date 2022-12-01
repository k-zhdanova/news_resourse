<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkCategoryRequest;
use App\Models\LinkCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Category of Links
 *
 * API для работы с категориями ссылок.
 */
class LinkCategoryController extends Controller
{

    /**
     * List of link category
     *
     * @param LinkCategoryRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */

    public function index(LinkCategoryRequest $request)
    {
        $linkCategory = new LinkCategory();
        $list = $linkCategory->getAll($request);
        return response()->json($list);
    }

    /**
     * Create link category
     *
     * @authenticated
     * @param LinkCategoryRequest $request
     *
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из тиблици Link_categories
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     */
    public function create(LinkCategoryRequest $request)
    {
        $linkCategory = new LinkCategory($request->all());

        if ($request->status == 1) {
            $linkCategory->published_at = Carbon::now()->toDateTimeString();
        } else {
            $linkCategory->published_at = NULL;
        }

        if ($request->parent_id) {
            $linkCategory->parent_id = LinkCategory::findOrCreateNewInstance($request->parent_id);
        }

        $linkCategory->save();

        return response()->json($linkCategory);
    }

    /**
     * Get specified link category
     *
     * @param LinkCategoryRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */
    public function show(LinkCategoryRequest $request, $id)
    {
        try {
            $linkCategory = LinkCategory::with(['parent'])
                ->with(['childs' => function ($q) {
                    $q->published();
                }])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($linkCategory);
    }

    /**
     * Update specified link category
     *
     * @authenticated
     * @param LinkCategoryRequest $request
     * @param $id
     * @return JsonResponse
     * @bodyParam name:en string optional Название на английском
     * @bodyParam name:uk string optional Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из тиблици Link_categories
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     */
    public function update(LinkCategoryRequest $request, $id)
    {
        try {
            $linkCategory = LinkCategory::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $linkCategory->fill($request->all());


        if ($request->status == 1 && !$linkCategory->published_at) {
            $linkCategory->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $linkCategory->published_at) {
            $linkCategory->published_at = null;
        }

        if ($request->parent_id) {
            $linkCategory->parent_id = LinkCategory::findOrCreateNewInstance($request->parent_id);
        }

        $linkCategory->save();


        return response()->json($linkCategory);
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
            $linkCategory = LinkCategory::withCount(['links', 'childs'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($linkCategory->links_count || $linkCategory->childs_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $linkCategory->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
