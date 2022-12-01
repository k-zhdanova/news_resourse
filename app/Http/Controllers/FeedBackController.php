<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedBackRequest;
use App\Models\FeedBack;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Feedbacks
 *
 * API для работы с отзывами.
 */
class FeedBackController extends Controller
{
    /**
     * List of feedbacks
     *
     * @param FeedBackRequest $request
     * @return JsonResponse
     * @queryParam page Номер страницы с результатами выдачи
     * @queryParam sort Поле для сортировки. По-умолчанию  'id\|desc'
     * @queryParam search Строка, которая должна содержаться в результатах выдачи
     * @queryParam user_id ID пользователя
     */

    public function index(FeedBackRequest $request)
    {
        $feedBack = new FeedBack();
        $feedBacks = $feedBack->getAll($request);

        $stat = collect([
            'stat' => [
                'today'     => $feedBack->countAll($request, Carbon::now()->startOfDay()),
                'yesterday' => $feedBack->countAll($request, Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()),
                'week'      => $feedBack->countAll($request, Carbon::now()->startOfWeek()),
                'month'     => $feedBack->countAll($request, Carbon::now()->startOfMonth()),
                'year'      => $feedBack->countAll($request, Carbon::now()->startOfYear())
            ],
            'stat_website' => [
                'today'     => $feedBack->countAvg($request, 'website', Carbon::now()->startOfDay()),
                'yesterday' => $feedBack->countAvg($request, 'website', Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()),
                'week'      => $feedBack->countAvg($request, 'website', Carbon::now()->startOfWeek()),
                'month'     => $feedBack->countAvg($request, 'website', Carbon::now()->startOfMonth()),
                'year'      => $feedBack->countAvg($request, 'website', Carbon::now()->startOfYear()),
                'all'       => $feedBack->countAvg($request, 'website')
            ],
            'stat_impression' => [
                'today'     => $feedBack->countAvg($request, 'impression', Carbon::now()->startOfDay()),
                'yesterday' => $feedBack->countAvg($request, 'impression', Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()),
                'week'      => $feedBack->countAvg($request, 'impression', Carbon::now()->startOfWeek()),
                'month'     => $feedBack->countAvg($request, 'impression', Carbon::now()->startOfMonth()),
                'year'      => $feedBack->countAvg($request, 'impression', Carbon::now()->startOfYear()),
                'all'       => $feedBack->countAvg($request, 'impression')
            ]
        ]);

        return response()->json($stat->merge($feedBacks));
    }


    /**
     * Create feedback
     *
     * @param FeedBackRequest $request
     * @return JsonResponse
     * @bodyParam name optional string Имя
     * @bodyParam email optional string Email адрес
     * @bodyParam text optional string Описание, текст заявки
     * @bodyParam date required string Дата обращения
     * @bodyParam sex required string Пол (male, female)
     * @bodyParam age required int Возраст
     * @bodyParam service_id optional int ID услуги
     * @bodyParam is_satisfied required int Удовлетворенность работой ЦНАП (1, 2)
     * @bodyParam reception_friendly required int Ресепшн, приветливость (1, 2, 3, 4, 5)
     * @bodyParam reception_competent required int Ресепшн, компетентность (1, 2, 3, 4, 5)
     * @bodyParam center_friendly required int Специалист, приветливость (1, 2, 3, 4, 5)
     * @bodyParam center_competent required int Специалист, компетентность (1, 2, 3, 4, 5)
     * @bodyParam website required int Оценка веб-сайта (1, 2, 3, 4, 5)
     * @bodyParam impression required int Общее впечатление (1, 2, 3, 4, 5)
     */
    public function create(FeedBackRequest $request)
    {
        $feedBack = new FeedBack($request->all());

        if ($request->status !== null) {
            $feedBack->status = $request->status;
        }

        $feedBack->fill($request->all());

        $feedBack->save();

        return response()->json($feedBack);
    }

    /**
     * Get specified feedback
     *
     * @param FeedBackRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(FeedBackRequest $request, $id)
    {
        try {
            $feedBack = FeedBack::with(['updatedBy', 'service'])->findOrFail($id);

            if ($feedBack->date) {
                $feedBack->date = date('d.m.Y', strtotime($feedBack->date));
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found.'
            ], 404);
        }

        return response()->json($feedBack);
    }

    /**
     * Update specified feedback
     *
     * @param FeedBackRequest $request
     * @param string $id
     *
     *
     * @return JsonResponse
     * @bodyParam status required int (1 - очікує відповіді,2 - дана відповідь,3 - відхилено)
     */
    public function update(FeedBackRequest $request, $id)
    {
        try {
            $feedBack = FeedBack::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        if ($request->status !== null) {
            $feedBack->status = $request->status;
        }

        if ($request->user()) {
            $feedBack->updated_by = $request->user()->id;
        }

        $feedBack->fill($request->all());

        $feedBack->save();

        return response()->json($feedBack);
    }
}
