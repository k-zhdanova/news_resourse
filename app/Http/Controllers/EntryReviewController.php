<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryReviewRequest;
use App\Models\EntryReview;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * @group Entry Reviews
 *
 * API для работы с комментариями к заявкам
 */
class EntryReviewController extends Controller
{
    /**
     * List of entry reviews
     *
     * @param EntryReviewRequest $request
     * @return Response
     * @queryParam page Номер страницы с результатами выдачи
     * @queryParam sort Поле для сортировки. По-умолчанию  'id\|desc'
     * @queryParam search Строка, которая должна содержаться в результатах выдачи
     * @queryParam entry_id ID заявки
     * @queryParam user_id ID пользователя
     */

    public function index(EntryReviewRequest $request)
    {
        $review = new EntryReview();
        $reviews = $review->getAll($request);
        return response()->json($reviews);
    }


    /**
     * Create an entry review
     *
     * @param EntryReviewRequest $request
     * @return Response
     * @bodyParam entry_id number required ID заяки
     * @bodyParam user_id number required ID пользователя
     * @bodyParam text string required Описание, текст заявки
     */
    public function create(EntryReviewRequest $request)
    {
        $review = new EntryReview($request->all());
        $review->user_id = $request->user()->id;

        $review->save();

        return response()->json($review);
    }

    /**
     * Get specified entry review
     *
     * @param EntryReviewRequest $request
     * @param string $id
     * @return Response
     */
    public function show(EntryReviewRequest $request, $id)
    {
        try {
            $review = EntryReview::with(['user', 'entry'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found.'
            ], 404);
        }

        return response()->json($review);
    }

    /**
     * Update specified entry review
     *
     * @param EntryReviewRequest $request
     * @param string $id
     * @bodyParam entry_id number required ID заяки
     * @bodyParam user_id number required ID пользователя
     * @bodyParam text string required Описание, текст заявки
     */
    public function update(EntryReviewRequest $request, $id)
    {
        try {
            $review = EntryReview::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $review->fill($request->all());

        if ($request->hide) {
            $review->hided_at = Carbon::now()->toDateTimeString();
        }

        if ($request->show) {
            $review->hided_at = NULL;
        }

        $review->save();

        return response()->json($review);
    }

    /**
     * Delete specified entry review
     *
     * @param string $id
     * @return Response
     */
    public function delete($id)
    {
        try {
            $review = EntryReview::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
