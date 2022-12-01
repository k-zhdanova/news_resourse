<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageCategoryRequest;
use App\Models\PageCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Category of Pages
 *
 * API для работы с категориями статических страниц.
 */
class PageCategoryController extends Controller
{

    /**
     * List of page category
     *
     * @param PageCategoryRequest $request
     *
     * @return JsonResponse
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */

    public function index(PageCategoryRequest $request)
    {
        $pageCategory = new PageCategory();
        $list = $pageCategory->getAll($request);
        return response()->json($list);
    }

    /**
     * Create page category
     *
     * @authenticated
     * @param PageCategoryRequest $request
     *
     * @return JsonResponse
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из тиблици Link_categories
     */
    public function create(PageCategoryRequest $request)
    {
        $pageCategory = new PageCategory($request->all());

        if ($request->parent_id) {
            $pageCategory->parent_id = PageCategory::findOrCreateNewInstance($request->parent_id);
        }

        $pageCategory->save();

        return response()->json($pageCategory);
    }

    /**
     * Get specified page category
     *
     * @param PageCategoryRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     */
    public function show(PageCategoryRequest $request, $id)
    {
        try {
            $pageCategory = PageCategory::with(['parent'])
                ->with('childs')
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($pageCategory);
    }

    /**
     * Update specified page category
     *
     * @authenticated
     * @param PageCategoryRequest $request
     * @param $id
     * @return JsonResponse
     * @bodyParam name:en string optional Название на английском
     * @bodyParam name:uk string optional Название на украинском
     * @bodyParam parent_id integer optional ID родительской категории из тиблици Link_categories
     */
    public function update(PageCategoryRequest $request, $id)
    {
        try {
            $pageCategory = PageCategory::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $pageCategory->fill($request->all());

        if ($request->parent_id) {
            $pageCategory->parent_id = PageCategory::findOrCreateNewInstance($request->parent_id);
        }

        $pageCategory->save();


        return response()->json($pageCategory);
    }

    /**
     * Delete specified page category
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $pageCategory = PageCategory::withCount(['pages', 'childs'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($pageCategory->pages_count || $pageCategory->childs_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $pageCategory->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
