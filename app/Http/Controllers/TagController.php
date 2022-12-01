<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @group Tags
 *
 * API для работы со поисковыми запросами (тегами).
 */

class TagController extends Controller
{
    /**
     * List of tags
     *
     * @param  TagRequest  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam status string Статус отображения (возможные значения visible, hidden)
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */

    public function index(TagRequest $request)
    {
        $tag  = new Tag();
        $tags = $tag->getAll($request);
        return response()->json($tags);
    }

    /**
     * Create tag
     *
     * @authenticated
     * @param  TagRequest  $request
     * 
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @return Response
     */
    public function create(TagRequest $request)
    {        
        $tag = new Tag($request->all());

        $tag->save();

        return response()->json($tag);
    }

    /**
     * Get specified tag
     *
     * @param  TagRequest  $request
     * @param  string  $id
     * @queryParam lang string На каком языке возвращать результаты (возможные значения uk, en)
     * @return Response
     */
    public function show(TagRequest $request, $id)
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($tag);
    }

    /**
     * Update specified tag
     *
     * @authenticated
     * @param  TagRequest  $request
     * @param  string  $id
     * @bodyParam name:en string required Название на английском
     * @bodyParam name:uk string required Название на украинском
     * @return Response
     */
    public function update(TagRequest $request, $id)
    {        
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $tag->fill($request->all());
        $tag->save();

        return response()->json($tag);
    }

    /**
     * Delete specified tag
     *
     * @authenticated
     * @param  TagRequest  $request
     * @param  string  $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $tag->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
