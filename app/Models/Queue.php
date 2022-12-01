<?php

namespace App\Models;

use App\Http\Requests\QueueRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;

class Queue extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'is_cron',
        'slot_duration',
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun',
        'break',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'queues_services_rel', 'queue_id');
    }

    public function getAll(QueueRequest $request)
    {
        $items = Queue::query()->select()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'queue');
            })
            ->when($request->status === 'hidden', function ($query) use ($request) {
                $query->where('status', 0);
            })
            ->when($request->status === 'visible', function ($query) use ($request) {
                $query->where('status', 1);
            })
            ->with('services')
            ->paginate();

        return $items;
    }

    public function setServices(QueueRequest $request)
    {
        $service_ids = [];
        Log::info('qwqw:' . $this->id);
        if ($request->service_id) {

            $service_ids = $request->service_id;

            if (!is_array($service_ids)) {
                $service_ids = [$service_ids];
            }
        }

        $services_to_delete = QueueService::where('queue_id', $this->id)
            ->whereNotIn('service_id', $service_ids)
            ->get();

        foreach ($services_to_delete as $service_to_delete) {
            $service_to_delete->delete();
        }

        $exist_services = QueueService::where('queue_id', $this->id)->get();
        $exist_services = $exist_services->pluck('service_id');

        foreach ($service_ids as $service_id) {

            if (!$exist_services->contains($service_id)) {
                QueueService::create([
                    'service_id' => $service_id,
                    'queue_id' => $this->id,
                ]);
            }
        }
    }
}
