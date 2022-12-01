<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Page;
use App\Http\Requests\PageRequest;
use App\Models\PageCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * @group Pages
 *
 * API для работы со статическими страницами.
 */
class PageController extends Controller
{

    /**
     * List of page
     *
     * @param PageRequest $request
     *
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */

    public function index(PageRequest $request)
    {
        $page = new Page();
        $page = $page->getAll($request);
        return response()->json($page);
    }

    /**
     * Create page
     *
     * @authenticated
     * @param PageRequest $request
     *
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam text:en string  Текст новости на английском
     * @bodyParam text:uk string  Текст новости на украинском
     * @bodyParam category_id integer required ID Категории страниц
     * @bodyParam meta_title:en string  Тег title на английском
     * @bodyParam meta_title:uk string  Тег title на украинском
     * @bodyParam meta_description:en string  Тег description на английском
     * @bodyParam meta_description:uk string  Тег description на украинском
     * @bodyParam image string  Картинка в base64
     * @bodyParam filename1:uk string optional Название файла 1 на украинском
     * @bodyParam filename1:en string optional Название файла 1 на английском
     * @bodyParam filename2:uk string optional Название файла 2 на украинском
     * @bodyParam filename2:en string optional Название файла 2 на английском
     * @bodyParam filename3:uk string optional Название файла 3 на украинском
     * @bodyParam filename3:en string optional Название файла 3 на английском
     * @bodyParam file_1 string optional Файл1 к услуге
     * @bodyParam file_2 string optional Файл2 к услуге
     * @bodyParam file_3 string optional Файл3 к услуге
     * @return JsonResponse
     */
    public function create(PageRequest $request)
    {
        $page = new Page($request->all());

        if ($request->image) {
            $page->image = ImageHelper::create_image_from_base64('pages', $request->image, Page::IMAGE_CONFIG);
        }

        if ($request->{'meta_title:uk'} == $page->{'name:uk'}) {
            $page->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $page->{'name:en'}) {
            $page->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Page::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $page->uri = $uri;
        }

        if ($request->file_1) {
            $page->file1 = $page->saveFile($request->file_1);
        }

        if ($request->file_2) {
            $page->file2 = $page->saveFile($request->file_2);
        }

        if ($request->file_3) {
            $page->file3 = $page->saveFile($request->file_3);
        }

        if ($request->category_id) {
            $page->category_id = PageCategory::findOrCreateNewInstance($request->category_id);
        }

        $page->save();

        return response()->json($page);
    }

    /**
     * Get specified page
     *
     * @param PageRequest $request
     * @param string $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */
    public function show(PageRequest $request, $id)
    {
        try {
            $page = Page::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($page);
    }

    /**
     * Update specified page
     *
     * @authenticated
     * @param PageRequest $request
     * @param string $id
     *
     * @bodyParam name:en string Название на английском
     * @bodyParam name:uk string Название на украинском
     * @bodyParam text:en string  Текст новости на английском
     * @bodyParam text:uk string  Текст новости на украинском
     * @bodyParam category_id integer ID Категории страниц
     * @bodyParam meta_title:en string  Тег title на английском
     * @bodyParam meta_title:uk string  Тег title на украинском
     * @bodyParam meta_description:en string  Тег description на английском
     * @bodyParam meta_description:uk string  Тег description на украинском
     * @bodyParam image string  Картинка в base64
     * @bodyParam filename1:uk string optional Название файла 1 на украинском
     * @bodyParam filename1:en string optional Название файла 1 на английском
     * @bodyParam filename2:uk string optional Название файла 2 на украинском
     * @bodyParam filename2:en string optional Название файла 2 на английском
     * @bodyParam filename3:uk string optional Название файла 3 на украинском
     * @bodyParam filename3:en string optional Название файла 3 на английском
     * @bodyParam file_1 string optional Файл1 к услуге
     * @bodyParam file_2 string optional Файл2 к услуге
     * @bodyParam file_3 string optional Файл3 к услуге
     * @return JsonResponse
     */
    public function update(PageRequest $request, $id)
    {
        try {
            $page = Page::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $page->fill($request->all());

        if ($request->{'meta_title:uk'} == $page->{'name:uk'}) {
            $page->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $page->{'name:en'}) {
            $page->fill(['en' => ['meta_title' => NULL]]);
        }

        $process_file = function ($file, $page, $file_field) {
            if (!$file || !($page->$file_field ?? '')) {
                return;
            }

            $old_filename = $page->$file_field;
            $page->$file_field = $page->saveFile($file);

            if ($page->isDirty($file_field) && $old_filename != NULL) {
                Storage::disk('public')->delete(config('custom.page_files_path') . '/' . $old_filename);
            }
        };

        $process_file($request->file_1, $page, 'file1');
        $process_file($request->file_2, $page, 'file2');
        $process_file($request->file_3, $page, 'file3');

        if ($request->image) {

            if ($page->image) {
                ImageHelper::remove_image('pages/' . $page->image);
                $page->image = null;
            }

            $page->image = ImageHelper::create_image_from_base64('pages', $request->image, Page::IMAGE_CONFIG);
        }

        if (!$request->image && $request->image_delete) {
            if ($page->image) {
                ImageHelper::remove_image('pages/' . $page->image);
                $page->image = null;
            }
        }

        if ($request->category_id) {
            $page->category_id = PageCategory::findOrCreateNewInstance($request->category_id);
        }

        $page->save();

        return response()->json($page);
    }

    /**
     * Delete specified page
     *
     * @authenticated
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $page = Page::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        Storage::disk('public')->delete(config('custom.page_files_path') . '/' . $page->file1);
        Storage::disk('public')->delete(config('custom.page_files_path') . '/' . $page->file2);
        Storage::disk('public')->delete(config('custom.page_files_path') . '/' . $page->file2);
        $page->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
