<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Http\Requests\InstitutionRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @group Institutions of services
 *
 * API для работы с субъектами оказания услуг.
 */

class InstitutionController extends Controller
{

    /**
     * List of institutions
     *
     * @param  InstitutionRequest  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */

    public function index(InstitutionRequest $request)
    {
        $institution  = new Institution();
        $institutions = $institution->getAll($request);
        return response()->json($institutions);
    }

    /**
     * Create institution
     *
     * @authenticated
     * @param  InstitutionRequest  $request
     * 
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string optional Тег title на английском
     * @bodyParam meta_title:uk string optional Тег title на украинском
     * @bodyParam meta_description:en string optional Тег description на английском
     * @bodyParam meta_description:uk string optional Тег description на украинском
     * @bodyParam address:en string optional Адрес на английском
     * @bodyParam address:uk string optional Адрес на украинском
     * @bodyParam schedule:en string optional График работы на английском
     * @bodyParam schedule:uk string optional График работы на украинском
     * @bodyParam website string optional URL вебсайта
     * @bodyParam emails string[] optional Массив имейлов
     * @bodyParam phones string[] optional Массив телефонов
     * @bodyParam status integer optional Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function create(InstitutionRequest $request)
    {
        $institution = new Institution($request->all());

        if ($request->status == 1) {
            $institution->published_at = Carbon::now()->toDateTimeString();
        } else {
            $institution->published_at = NULL;
        }

        if ($request->phones) {
            $phones = array_filter($request->phones, function ($phone) {
                return strlen(trim($phone['phone'])) > 0;
            });
            $institution->phones = $phones;
        }

        if ($request->{'meta_title:uk'} == $institution->{'name:uk'}) {
            $institution->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $institution->{'name:en'}) {
            $institution->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Institution::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $institution->uri = $uri;
        }

        $institution->save();

        return response()->json($institution);
    }

    /**
     * Get specified institution
     *
     * @param  InstitutionRequest  $request
     * @param  string  $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */
    public function show(InstitutionRequest $request, $id)
    {
        try {
            $institution = Institution::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($institution);
    }

    /**
     * Update specified institution
     *
     * @authenticated
     * @param  InstitutionRequest  $request
     * @param  string  $id
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam address:en string optional Адрес на английском
     * @bodyParam address:uk string optional Адрес на украинском
     * @bodyParam schedule:en string optional График работы на английском
     * @bodyParam schedule:uk string optional График работы на украинском
     * @bodyParam website string optional URL вебсайта
     * @bodyParam emails string[] optional Массив имейлов
     * @bodyParam phones string[] optional Массив телефонов
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function update(InstitutionRequest $request, $id)
    {
        try {
            $institution = Institution::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($institution->{'name:uk'} != $request->{'name:uk'} && $institution->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Institution::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $institution->uri = $uri;
        }

        $institution->fill($request->all());

        if ($request->phones) {
            $phones = array_filter($request->phones, function ($phone) {
                return strlen(trim($phone['phone'])) > 0;
            });
            $institution->phones = $phones;
        } else {
            $institution->phones = NULL;
        }

        if ($request->{'meta_title:uk'} == $institution->{'name:uk'}) {
            $institution->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $institution->{'name:en'}) {
            $institution->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->status == 1 && !$institution->published_at) {
            $institution->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $institution->published_at) {
            $institution->published_at = null;
        }

        $institution->save();

        return response()->json($institution);
    }

    /**
     * Delete specified institution
     *
     * @authenticated
     * @param  InstitutionRequest  $request
     * @param  string  $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $institution = Institution::withCount('services')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($institution->services_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $institution->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
