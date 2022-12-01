<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\News;
use App\Http\Requests\NewsRequest;
use App\Models\NewsCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * @group News
 *
 * API для работы с новостями.
 */
class NewsController extends Controller
{

    /**
     * List of news
     *
     * @param NewsRequest $request
     *
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */

    public function index(NewsRequest $request)
    {
        $news = new News();
        $list = $news->getAll($request);
        return response()->json($list);
    }

    /**
     * Create news
     *
     * @authenticated
     * @param NewsRequest $request
     *
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam text:en string  Текст новости на английском
     * @bodyParam text:uk string  Текст новости на украинском
     * @bodyParam meta_title:en string  Тег title на английском
     * @bodyParam meta_title:uk string  Тег title на украинском
     * @bodyParam meta_description:en string  Тег description на английском
     * @bodyParam meta_description:uk string  Тег description на украинском
     * @bodyParam is_pinned integer  Закрепить нвоость на главной (1-новость закреплена)
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam image string  Картинка в base64
     * @bodyParam tag_id integer[] Массив IDs тегов optional
     * @return JsonResponse
     */
    public function create(NewsRequest $request)
    {
        $news = new News($request->all());

        if ($request->status == 1) {
            $news->published_at = Carbon::now()->toDateTimeString();
        } else {
            $news->published_at = NULL;
        }

        if ($request->image) {
            $news->image = ImageHelper::create_image_from_base64('news', $request->image, News::IMAGE_CONFIG);
        }

        if ($request->{'meta_title:uk'} == $news->{'name:uk'}) {
            $news->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $news->{'name:en'}) {
            $news->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (News::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $news->uri = $uri;
        }

        if ($request->category_id) {
            $news->category_id = NewsCategory::findOrCreateNewInstance($request->category_id);
        }

        // немедленная публикация
        if ($request->publish == 'now') {
            $news->published_at = Carbon::now()->toDateTimeString();
            $news->is_published = 1;
        }

        // отложенная публикация
        if ($request->publish == 'later') {
            $publish = $request->publish_date . ' ' . $request->publish_time;
            $news->published_at = Carbon::createFromFormat('Y-m-d H:i', $publish, 'Europe/Kiev')
                ->setTimezone('UTC')
                ->toDateTimeString();
            $news->is_published = 1;
        }

        $news->save();

        return response()->json($news);
    }

    /**
     * Get specified news
     *
     * @param NewsRequest $request
     * @param string $id
     *
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */
    public function show(NewsRequest $request, $id)
    {
        try {
            $news = News::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($news);
    }

    /**
     * Update specified news
     *
     * @authenticated
     * @param NewsRequest $request
     * @param string $id
     *
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam text:en string  Текст новости на английском
     * @bodyParam text:uk string  Текст новости на украинском
     * @bodyParam meta_title:en string  Тег title на английском
     * @bodyParam meta_title:uk string  Тег title на украинском
     * @bodyParam meta_description:en string  Тег description на английском
     * @bodyParam meta_description:uk string  Тег description на украинском
     * @bodyParam is_pinned integer  Закрепить новость на главной (1-новость закреплена)
     * @bodyParam status integer  Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam image string  Картинка в base64
     * @bodyParam tag_id integer[] Массив IDs тегов optional
     * @return JsonResponse
     */
    public function update(NewsRequest $request, $id)
    {
        try {
            $news = News::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($news->{'name:uk'} != $request->{'name:uk'} && $news->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (News::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $news->uri = $uri;
        }

        $news->fill($request->all());

        if ($request->{'meta_title:uk'} == $news->{'name:uk'}) {
            $news->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $news->{'name:en'}) {
            $news->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->image) {

            if ($news->image) {
                ImageHelper::remove_image('news/' . $news->image);
                $news->image = null;
            }

            $news->image = ImageHelper::create_image_from_base64('news', $request->image, News::IMAGE_CONFIG);
        }

        if (!$request->image && $request->image_delete) {
            if ($news->image) {
                ImageHelper::remove_image('news/' . $news->image);
                $news->image = null;
            }
        }

        if ($request->status == 1 && !$news->published_at) {
            $news->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $news->published_at) {
            $news->published_at = null;
        }

        if ($request->category_id) {
            $news->category_id = NewsCategory::findOrCreateNewInstance($request->category_id);
        }

        if (!$news->is_published) {
            // немедленная публикация
            if ($request->publish == 'now') {
                $news->published_at = Carbon::now()->toDateTimeString();
                $news->is_published = 1;
            }

            // отложенная публикация
            if ($request->publish == 'later') {
                $publish = $request->publish_date . ' ' . $request->publish_time;
                $news->published_at = Carbon::createFromFormat('Y-m-d H:i', $publish, 'Europe/Kiev')
                    ->setTimezone('UTC')
                    ->toDateTimeString();
                $news->is_published = 1;
            }
        } else {

            // снятие с публикации
            if ($request->is_published == 0) {
                $news->published_at = NULL;
                $news->is_published = 0;
            }

            if ($request->publish_date && $request->publish_time) {
                // изменение времени публикации
                $publish = $request->publish_date . ' ' . $request->publish_time;
                $date = Carbon::createFromFormat('Y-m-d H:i', $publish, 'Europe/Kiev')
                    ->setTimezone('UTC')
                    ->toDateTimeString();
                if ($news->published_at != $date) {
                    $news->published_at = $date;
                }
            }
        }

        $news->save();

        return response()->json($news);
    }

    /**
     * Delete specified news
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $news = News::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $news->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
