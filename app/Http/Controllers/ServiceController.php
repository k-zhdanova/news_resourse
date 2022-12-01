<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Sector;
use App\Models\Institution;
use App\Models\Category;
use App\Http\Requests\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @group Services
 *
 * API для работы с услугами.
 */
class ServiceController extends Controller
{

    /**
     * List of services
     *
     * @param ServiceRequest $request
     *
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam service_id integer ID сферы услуг
     * @queryParam category_id integer ID категории услуг
     * @queryParam institution_id integer ID субъекта оказания услуг
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */

    public function index(ServiceRequest $request)
    {
        $service = new Service();
        $services = $service->getAll($request);

        return response()->json($services);
    }

    /**
     * Create service
     *
     * @authenticated
     * @param ServiceRequest $request
     *
     * @bodyParam code string Номер (код) услуги
     * @bodyParam sector_id integer required ID сферы услуг
     * @bodyParam category_id integer required ID категории услуг
     * @bodyParam institution_id integer required ID субъекта оказания услуг
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam text:en string optional Описание услуги на английском
     * @bodyParam text:uk string optional Описание услуги на украинском
     * @bodyParam meta_title:en string optional Тег title на английском
     * @bodyParam meta_title:uk string optional Тег title на украинском
     * @bodyParam meta_description:en string optional Тег description на английском
     * @bodyParam meta_description:uk string optional Тег description на украинском
     * @bodyParam is_online integer optional Возможность заказать услугу онлайн (1-онлайн, 0-не онлайн)
     * @bodyParam status integer optional Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam tag_id integer[] Массив IDs тегов optional
     * @bodyParam filename1:uk string optional Название файла 1 на украинском
     * @bodyParam filename1:en string optional Название файла 1 на английском
     * @bodyParam filename2:uk string optional Название файла 2 на украинском
     * @bodyParam filename2:en string optional Название файла 2 на английском
     * @bodyParam place:uk string optional Место оказания услуги на украинском
     * @bodyParam place:en string optional Место оказания услуги на английском
     * @bodyParam term:uk string optional Срок на украинском
     * @bodyParam term:en string optional Срок 2 на английском
     * @bodyParam is_free integer optional Стоимость услуги (1-бесплатно, 0-платно)
     * @bodyParam file_1 string optional Файл1 к услуге
     * @bodyParam file_2 string optional Файл2 к услуге
     * @return JsonResponse
     */
    public function create(ServiceRequest $request)
    {
        $service = new Service($request->all());

        if ($request->status == 1) {
            $service->published_at = Carbon::now()->toDateTimeString();
        } else {
            $service->published_at = NULL;
        }

        if ($request->{'meta_title:uk'} == $service->{'name:uk'}) {
            $service->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $service->{'name:en'}) {
            $service->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Service::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $service->uri = $uri;
        }

        if ($request->file_1) {
            $service->file1 = $service->saveFile($request->file_1);
        }

        if ($request->file_2) {
            $service->file2 = $service->saveFile($request->file_2);
        }

        if ($request->sector_id) {
            $service->sector_id = Sector::findOrCreateNewInstance($request->sector_id);
        }

        if ($request->category_id) {
            $service->category_id = Category::findOrCreateNewInstance($request->category_id);
        }

        if ($request->institution_id) {
            $service->institution_id = Institution::findOrCreateNewInstance($request->institution_id);
        }

        $service->save();

        $service->setTags($request);


        return response()->json($service);
    }

    /**
     * Get specified service
     *
     * @param ServiceRequest $request
     * @param string $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return JsonResponse
     */
    public function show(ServiceRequest $request, $id)
    {
        try {
            $service = Service::with('tags', 'sector', 'institution', 'category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($service);
    }

    /**
     * Update specified service
     *
     * @authenticated
     * @param ServiceRequest $request
     * @param string $id
     *
     * @bodyParam code string Номер (код) услуги
     * @bodyParam sector_id integer required ID сферы услуг
     * @bodyParam category_id integer required ID категории услуг
     * @bodyParam institution_id integer required ID субъекта оказания услуг
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam text:en string optional Описание услуги на английском
     * @bodyParam text:uk string optional Описание услуги на украинском
     * @bodyParam meta_title:en string optional Тег title на английском
     * @bodyParam meta_title:uk string optional Тег title на украинском
     * @bodyParam meta_description:en string optional Тег description на английском
     * @bodyParam meta_description:uk string optional Тег description на украинском
     * @bodyParam is_online integer optional Возможность заказать услугу онлайн (1-онлайн, 0-не онлайн)
     * @bodyParam status integer optional Статус отображения (1-отображается, 0-скрыто)
     * @bodyParam tag_id integer[] Массив IDs тегов optional
     * @bodyParam filename1:uk string optional Название файла 1 на украинском
     * @bodyParam filename1:en string optional Название файла 1 на английском
     * @bodyParam filename2:uk string optional Название файла 2 на украинском
     * @bodyParam filename2:en string optional Название файла 2 на английском
     * @bodyParam place:uk string optional Место оказания услуги на украинском
     * @bodyParam place:en string optional Место оказания услуги на английском
     * @bodyParam term:uk string optional Срок на украинском
     * @bodyParam term:en string optional Срок 2 на английском
     * @bodyParam is_free integer optional Стоимость услуги (1-бесплатно, 0-платно)
     * @bodyParam file_1 string optional Файл1 к услуге
     * @bodyParam file_2 string optional Файл2 к услуге
     * @return JsonResponse
     */
    public function update(ServiceRequest $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($service->{'name:uk'} != $request->{'name:uk'} && $service->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Service::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $service->uri = $uri;
        }

        $service->fill($request->all());

        if ($request->{'meta_title:uk'} == $service->{'name:uk'}) {
            $service->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $service->{'name:en'}) {
            $service->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->status == 1 && !$service->published_at) {
            $service->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $service->published_at) {
            $service->published_at = null;
        }

        $process_file = function ($file, $service, $file_field) {
            if (!$file || !($service->$file_field ?? '')) {
                return;
            }

            $old_filename = $service->$file_field;
            $service->$file_field = $service->saveFile($file);

            if ($service->isDirty($file_field) && $old_filename != NULL) {
                Storage::disk('public')->delete(config('custom.service_files_path') . '/' . $old_filename);
            }
        };

        $process_file($request->file_1, $service, 'file1');
        $process_file($request->file_2, $service, 'file2');

        if ($request->sector_id) {
            $service->sector_id = Sector::findOrCreateNewInstance($request->sector_id);
        }

        if ($request->category_id) {
            $service->category_id = Category::findOrCreateNewInstance($request->category_id);
        }

        if ($request->institution_id) {
            $service->institution_id = Institution::findOrCreateNewInstance($request->institution_id);
        }

        $service->save();

        $service->setTags($request);

        return response()->json($service);
    }

    /**
     * Delete specified service
     *
     * @authenticated
     * @param ServiceRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $service = Service::withCount(['entries', 'queues', 'feedbacks'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($service->entries_count || $service->queues_count || $service->feedbacks_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        Storage::disk('public')->delete(config('custom.service_files_path') . '/' . $service->file1);
        Storage::disk('public')->delete(config('custom.service_files_path') . '/' . $service->file2);
        $service->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
