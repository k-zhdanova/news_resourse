<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Http\Requests\SectorRequest;
use App\Models\Sector;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @group Sectors of services
 *
 * API для работы со сферами услуг.
 */

class SectorController extends Controller
{

    /**
     * List of sectors
     *
     * @param  SectorRequest  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */

    public function index(SectorRequest $request)
    {
        $sector  = new Sector();
        $sectors = $sector->getAll($request);
        return response()->json($sectors);
    }

    /**
     * Create sector
     *
     * @authenticated
     * @param  SectorRequest  $request
     * 
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string optional Тег title на английском
     * @bodyParam meta_title:uk string optional Тег title на украинском
     * @bodyParam meta_description:en string optional Тег description на английском
     * @bodyParam meta_description:uk string optional Тег description на украинском
     * @bodyParam image string optional Картинка в base64
     * @bodyParam status integer optional Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function create(SectorRequest $request)
    {
        $sector = new Sector($request->all());

        if ($request->status == 1) {
            $sector->published_at = Carbon::now()->toDateTimeString();
        } else {
            $sector->published_at = NULL;
        }

        if ($request->{'meta_title:uk'} == $sector->{'name:uk'}) {
            $sector->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $sector->{'name:en'}) {
            $sector->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->image) {
            $sector->image = ImageHelper::create_image_from_base64('sectors', $request->image, Sector::IMAGE_CONFIG);
        }

        if ($request->{'name:uk'}) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Sector::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $sector->uri = $uri;
        }

        $sector->save();

        return response()->json($sector);
    }

    /**
     * Get specified sector
     *
     * @param  SectorRequest  $request
     * @param  string  $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */
    public function show(SectorRequest $request, $id)
    {
        try {
            $sector = Sector::with(['categories', 'services'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($sector);
    }

    /**
     * Update specified sector
     *
     * @authenticated
     * @param  SectorRequest  $request
     * @param  string  $id
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @bodyParam meta_title:en string Тег title на английском
     * @bodyParam meta_title:uk string Тег title на украинском
     * @bodyParam meta_description:en string Тег description на английском
     * @bodyParam meta_description:uk string Тег description на украинском
     * @bodyParam image string optional Картинка в base64
     * @bodyParam image_delete integer optional Удаление картинки (1-удалить)
     * @bodyParam status integer Статус отображения (1-отображается, 0-скрыто)
     * @return Response
     */
    public function update(SectorRequest $request, $id)
    {
        try {
            $sector = Sector::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($sector->{'name:uk'} != $request->{'name:uk'} && $sector->published_at == null) {
            $uri = Str::slug($request->{'name:uk'}, '-');
            $i = 1;
            while (Sector::where('uri', $uri)->withTrashed()->count()) {
                $uri = $uri . '-' . $i++;
            }
            $sector->uri = $uri;
        }

        $sector->fill($request->all());

        if ($request->{'meta_title:uk'} == $sector->{'name:uk'}) {
            $sector->fill(['uk' => ['meta_title' => NULL]]);
        }

        if ($request->{'meta_title:en'} == $sector->{'name:en'}) {
            $sector->fill(['en' => ['meta_title' => NULL]]);
        }

        if ($request->image) {

            if ($sector->image) {
                ImageHelper::remove_image('sectors/' . $sector->image);
                $sector->image = null;
            }

            $sector->image = ImageHelper::create_image_from_base64('sectors', $request->image, Sector::IMAGE_CONFIG);
        }

        if (!$request->image && $request->image_delete) {
            if ($sector->image) {
                ImageHelper::remove_image('sectors/' . $sector->image);
                $sector->image = null;
            }
        }

        if ($request->status == 1 && !$sector->published_at) {
            $sector->published_at = Carbon::now()->toDateTimeString();
        }

        if ($request->status != 1 && $sector->published_at) {
            $sector->published_at = null;
        }

        $sector->save();


        return response()->json($sector);
    }

    /**
     * Delete specified sector
     *
     * @authenticated
     * @param  SectorRequest  $request
     * @param  string  $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $sector = Sector::withCount(['services', 'categories'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($sector->categories_count || $sector->services_count) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.forbidden')
            ], 403);
        }

        $sector->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
