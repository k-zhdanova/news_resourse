<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryRequest;
use App\Models\Entry;
use App\Models\EntryFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

/**
 * @group Entries
 *
 * API для работы с входными заявками
 */
class EntryController extends Controller
{
    /**
     * List of entries
     *
     * @param EntryRequest $request
     * @return JsonResponse
     * @queryParam page Номер страницы с результатами выдачи
     * @queryParam sort Поле для сортировки. По-умолчанию  'id\|desc'
     * @queryParam search Строка, которая должна содержаться в результатах выдачи
     * @queryParam service_id ID сервиса
     * @queryParam user_id ID пользователя
     */

    public function index(EntryRequest $request)
    {
        $entry = new Entry();
        $entries = $entry->getAll($request);

        $stat = collect([
            'stat' => [
                'today'     => $entry->countAll($request, Carbon::now()->startOfDay()),
                'yesterday' => $entry->countAll($request, Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()),
                'week'      => $entry->countAll($request, Carbon::now()->startOfWeek()),
                'month'     => $entry->countAll($request, Carbon::now()->startOfMonth()),
                'year'      => $entry->countAll($request, Carbon::now()->startOfYear())
            ]
        ]);

        return response()->json($stat->merge($entries));
    }


    /**
     * Create an entry
     *
     * @param EntryRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @bodyParam user_id required ID устройсва
     * @bodyParam service_id required ID категории
     * @bodyParam text required string Описание, текст заявки
     * @bodyParam phone required string Контактный телефон
     * @bodyParam status optional string (active,finished,refused)
     * @bodyParam file [] optional Документы к заявке(если есть)
     */
    public function create(EntryRequest $request)
    {
        $entry = new Entry($request->all());

        if ($request->status !== null) {
            $entry->status($request, $entry);
        }

        $entry->user_id = $request->user()->id;

        if ($file = $request->hasFile('file')) {
            $y = date('Y');
            $m = date('m');
            $d = date('d');

            $path = "entries/{$y}/{$m}/{$d}";
            Storage::disk('public')->put($path, $file);
        }

        $entry->save();

        if ($request->hasFile('files')) {
            $file = new EntryFile();
            $file->saveFile($request, $entry->id);
        }

        return response()->json($entry);
    }

    /**
     * Get specified entry
     *
     * @param EntryRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(EntryRequest $request, $id)
    {
        try {
            $entry = Entry::with(['user', 'service', 'files', 'reviews'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found.'
            ], 404);
        }

        return response()->json($entry);
    }

    /**
     * Update specified entry
     *
     * @param EntryRequest $request
     * @param string $id
     *
     *
     * @return JsonResponse
     * @throws ValidationException
     * @bodyParam user_id optional ID устройсва
     * @bodyParam service_id optional ID категории
     * @bodyParam text optional string Описание, текст заявки
     * @bodyParam status optional string (active,finished,refused)
     * @bodyParam phone optional string Контактный телефон
     */
    public function update(EntryRequest $request)
    {
        try {
            $entry = Entry::findOrFail($request->user()->id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($request->status !== null) {
            $entry->status($request, $entry);
        }

        $entry->fill($request->all());

        $entry->save();

        return response()->json($entry);
    }
}
