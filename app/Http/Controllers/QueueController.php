<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueRequest;
use App\Models\Queue;
use App\Models\QueueService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Queues
 *
 * API для работы с очередями
 */

class QueueController extends Controller
{

    /**
     * List of queues
     *
     * @param  QueueRequest  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'id|desc'
     * @queryParam status integer Статус очереди
     * @return JsonResponse
     */

    public function index(QueueRequest $request)
    {
        $queue   = new Queue();
        $queues = $queue->getAll($request);

        return response()->json($queues);
    }

    /**
     * Create queue
     *
     * @param  QueueRequest  $request
     * 
     * @authenticated
     * @bodyParam name string required Название очереди
     * @bodyParam status integer optional Статус очереди (1 - активная, 0 - не активная)
     * @bodyParam is_cron integer optional Название очереди
     * @bodyParam slot_duration integer optional Длительность слота, мин
     * @bodyParam mon string optional Время работы в понедельник (08:00-17:00)
     * @bodyParam tue string optional Время работы во вторник
     * @bodyParam wed string optional Время работы в среду
     * @bodyParam thu string optional Время работы в четверг
     * @bodyParam fri string optional Время работы в пятницу
     * @bodyParam sat string optional Время работы в субботу
     * @bodyParam sun string optional Время работы в воскресенье
     * @bodyParam break string optional Время перерыва (12:00-13:00)
     * @bodyParam service_id integer[] optional Массив IDs сервисов
     * @return JsonResponse
     */
    public function create(QueueRequest $request)
    {
        $queue = new Queue($request->all());
        $queue->save();

        $queue->setServices($request);

        return response()->json($queue);
    }

    /**
     * Get specified queue
     *
     * @param  QueueRequest  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(QueueRequest $request, $id)
    {
        try {
            $queue = Queue::with(['services'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($queue);
    }

    /**
     * Update queue
     *
     * @authenticated
     * @param  QueueRequest  $request
     * @param  string  $id
     * @bodyParam name string required Название очереди
     * @bodyParam status integer optional Статус очереди (1 - активная, 0 - не активная)
     * @bodyParam is_cron integer optional Название очереди
     * @bodyParam slot_duration integer optional Длительность слота, мин
     * @bodyParam mon string optional Время работы в понедельник (08:00-17:00)
     * @bodyParam tue string optional Время работы во вторник
     * @bodyParam wed string optional Время работы в среду
     * @bodyParam thu string optional Время работы в четверг
     * @bodyParam fri string optional Время работы в пятницу
     * @bodyParam sat string optional Время работы в субботу
     * @bodyParam sun string optional Время работы в воскресенье
     * @bodyParam break string optional Время перерыва (12:00-13:00)
     * @bodyParam service_id integer[] optional Массив IDs сервисов
     * @return JsonResponse
     */
    public function update(QueueRequest $request, $id)
    {
        try {
            $queue = Queue::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $queue->fill($request->all());
        $queue->save();

        $queue->setServices($request);

        return response()->json($queue);
    }

    /**
     * Delete specified queue
     *
     * @authenticated
     * @param  QueueRequest  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            $queue = Queue::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        QueueService::where('queue_id', $queue->id)
            ->delete();

        $queue->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
