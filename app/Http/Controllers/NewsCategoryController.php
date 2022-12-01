<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use App\Http\Requests\NewsCategoryRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @group Category of news
 *
 * API для работы со категориями новостей.
 */

class NewsCategoryController extends Controller
{
    /**
     * List of news categories
     *
     * @param  NewsCategoryRequest  $request
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @return Response
     */

    public function index(NewsCategoryRequest $request)
    {
        $newsCategory   = new NewsCategory();
        $newsCategories = $newsCategory->getAll($request);
        return response()->json($newsCategories);
    }

    /**
     * Create news category
     *
     * @authenticated
     * @param  NewsCategoryRequest  $request
     * 
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function create(NewsCategoryRequest $request)
    {
        $newsCategory = new NewsCategory($request->all());

        if ($request->status == 1) {
            $newsCategory->published_at = Carbon::now()->toDateTimeString();
        } else {
            $newsCategory->published_at = NULL;
        }

        if ($request->{'meta_title:uk'} == $newsCategory->{'name:uk'}) {
            $newsCategory->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $newsCategory->{'name:en'}) {
            $newsCategory->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (NewsCategory::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $newsCategory->uri = $uri;
        }

        $newsCategory->save();

        return response()->json($newsCategory);
    }

    /**
     * Get specified news category
     *
     * @param  NewsCategoryRequest  $request
     * @param  string  $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */
    public function show(NewsCategoryRequest $request, $id)
    {
        try {
            $newsCategory = NewsCategory::with(['news' => function ($q) {
                $q->published();
            }])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($newsCategory);
    }

    /**
     * Update specified news category
     *
     * @authenticated
     * @param  NewsCategoryRequest  $request
     * @param  string  $id
     * @bodyParam name:en string Название на английском
     * @bodyParam name:uk string Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function update(NewsCategoryRequest $request, $id)
    {
        try {
            $newsCategory = NewsCategory::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($newsCategory->{'name:uk'} != $request->{'name:uk'} && $newsCategory->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (NewsCategory::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $newsCategory->uri = $uri;
        }

        $newsCategory->fill($request->all());

        if ($request->{'meta_title:uk'} == $newsCategory->{'name:uk'}) {
            $newsCategory->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $newsCategory->{'name:en'}) {
            $newsCategory->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->status == 1 && !$newsCategory->published_at) {
            $newsCategory->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $newsCategory->published_at) {
            $newsCategory->published_at = null;
        }

        $newsCategory->save();

        return response()->json($newsCategory);
    }

    /**
     * Delete specified news category
     *
     * @authenticated
     * @param  NewsCategoryRequest  $request
     * @param  string  $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $newsCategory = NewsCategory::withCount('news')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($newsCategory->news_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $newsCategory->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
