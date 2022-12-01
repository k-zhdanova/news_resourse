<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Models\Sector;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @group Category of services
 *
 * API для работы со категориями услуг.
 */

class CategoryController extends Controller
{
    /**
     * List of categories
     *
     * @param  CategoryRequest  $request
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam sector_id integer ID сферы услуг
     * @return Response
     */

    public function index(CategoryRequest $request)
    {
        $category   = new Category();
        $categories = $category->getAll($request);
        return response()->json($categories);
    }

    /**
     * Create category
     *
     * @authenticated
     * @param  CategoryRequest  $request
     * 
     * @bodyParam sector_id integer required ID сферы услуг
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function create(CategoryRequest $request)
    {
        $category = new Category($request->all());

        if ($request->status == 1) {
            $category->published_at = Carbon::now()->toDateTimeString();
        } else {
            $category->published_at = NULL;
        }

        if ($request->{'meta_title:uk'} == $category->{'name:uk'}) {
            $category->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $category->{'name:en'}) {
            $category->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Category::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $category->uri = $uri;
        }

        if ($request->sector_id) {
            $category->sector_id = Sector::findOrCreateNewInstance($request->sector_id);
        }

        $category->save();

        return response()->json($category);
    }

    /**
     * Get specified category
     *
     * @param  CategoryRequest  $request
     * @param  string  $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */
    public function show(CategoryRequest $request, $id)
    {
        try {
            $category = Category::with(['sector'])
                ->with(['services' => function ($q) {
                    $q->published();
                }])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($category);
    }

    /**
     * Update specified category
     *
     * @authenticated
     * @param  CategoryRequest  $request
     * @param  string  $id
     * @bodyParam sector_id integer ID сферы услуг
     * @bodyParam name:en string Название на английском
     * @bodyParam name:uk string Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($category->{'name:uk'} != $request->{'name:uk'} && $category->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Category::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $category->uri = $uri;
        }

        $category->fill($request->all());

        if ($request->{'meta_title:uk'} == $category->{'name:uk'}) {
            $category->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $category->{'name:en'}) {
            $category->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->status == 1 && !$category->published_at) {
            $category->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $category->published_at) {
            $category->published_at = null;
        }

        if ($request->sector_id) {
            $category->sector_id = Sector::findOrCreateNewInstance($request->sector_id);
        }

        $category->save();

        return response()->json($category);
    }

    /**
     * Delete specified category
     *
     * @authenticated
     * @param  CategoryRequest  $request
     * @param  string  $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $category = Category::withCount('services')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($category->services_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $category->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
