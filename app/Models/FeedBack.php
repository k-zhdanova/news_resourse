<?php

namespace App\Models;

use App\Http\Requests\FeedBackRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class FeedBack extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    protected static $logAttributes = [
        'updated_by'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = ['status_name'];

    const STATUS_NAMES = ['Очікує відповіді', 'Дана відповідь', 'Відхилено'];

    public function getAll(FeedBackRequest $request)
    {

        $items = FeedBack::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'feed_backs');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('updatedBy', function ($query) use ($request) {
                        $query->where('firstname', 'LIKE', "%{$request->search}%");
                        $query->orWhere('lastname', 'LIKE', "%{$request->search}%");
                    });
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->updated_by, function ($query) use ($request) {
                $query->whereHas('updatedBy', function ($query) use ($request) {
                    $query->where('id', $request->updated_by);
                });
            })
            ->with('updatedBy')
            ->with('service')
            ->paginate();
        return $items;
    }

    public function countAll(FeedBackRequest $request, $from = null, $till = null)
    {
        return FeedBack::query()
            ->when($from, function($query) use ($from) {
                $from = $from->toDateTimeString();
                $query->where('created_at', '>=', $from);
            })
            ->when($till, function($query) use ($till) {
                $till = $till->toDateTimeString();
                $query->where('created_at', '<=', $till);
            })
            ->count();
    }

    public function countAvg(FeedBackRequest $request, $field = '', $from = null, $till = null)
    {
        if (!in_array($field, ['website', 'impression'])) {
            return 0;
        }

        $avg =  FeedBack::query()
            ->when($from, function($query) use ($from) {
                $from = $from->toDateTimeString();
                $query->where('created_at', '>=', $from);
            })
            ->when($till, function($query) use ($till) {
                $till = $till->toDateTimeString();
                $query->where('created_at', '<=', $till);
            })
            ->avg($field);

        return round($avg, 1);
    }

    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function getStatusNameAttribute()
    {
        $status = 1;
        if ($this->attributes['status']) {
            $status = $this->attributes['status'];
        }
        return FeedBack::STATUS_NAMES[$status - 1];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
