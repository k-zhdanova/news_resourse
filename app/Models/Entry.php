<?php

namespace App\Models;

use App\Http\Requests\EntryRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;


class Entry extends Model
{

    use HasFactory, LogsActivity;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'text',
        'phone',
        'started_at',
        'finished_at',
        'refused_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getAll(EntryRequest $request)
    {

        $items = Entry::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'entries');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('user', function ($query) use ($request) {
                        $query->where('firstname', 'LIKE', "%{$request->search}%");
                        $query->orWhere('lastname', 'LIKE', "%{$request->search}%");
                    });
                    $query->orWhereHas('service', function ($query) use ($request) {
                        $query->where('code', 'LIKE', "%{$request->search}%");
                        $query->orWhereTranslationLike('name', "%{$request->search}%");
                    });
                });
            })
            ->when($request->user_id, function ($query) use ($request) {
                $query->whereHas('user', function ($query) use ($request) {
                    $query->where('user_id', $request->user_id);
                });
            })
            ->when($request->service_id, function ($query) use ($request) {
                $query->whereHas('service', function ($query) use ($request) {
                    $query->where('service_id', $request->service_id);
                });
            })
            ->when($request->status == 'new', function ($query) {
                $query->whereNull('started_at');
                $query->whereNull('finished_at');
                $query->whereNull('refused_at');
            })
            ->when($request->status == 'active', function ($query) {
                $query->whereNotNull('started_at');
                $query->whereNull('finished_at');
                $query->whereNull('refused_at');
            })
            ->when($request->status == 'finished', function ($query) {
                $query->whereNotNull('finished_at');
                $query->whereNull('refused_at');
            })
            ->when($request->status == 'refused', function ($query) {
                $query->whereNotNull('refused_at');
            })
            ->with('user')
            ->with('service')
            ->paginate();
        return $items;
    }

    public function countAll(EntryRequest $request, $from = null, $till = null)
    {
        return Entry::query()
            ->when($from, function ($query) use ($from) {
                $from = $from->toDateTimeString();
                $query->where('created_at', '>=', $from);
            })
            ->when($till, function ($query) use ($till) {
                $till = $till->toDateTimeString();
                $query->where('created_at', '<=', $till);
            })
            ->count();
    }

    public function status(EntryRequest $request, $model)
    {
        if ($request->status == 'active' && !$model->started_at && !$model->finished_at && !$model->refused_at) {
            $model->started_at = Carbon::now()->toDateTimeString();
        }
        if ($request->status == 'finished' && !$model->finished_at && !$model->refused_at) {
            $model->finished_at = Carbon::now()->toDateTimeString();;
        }
        if ($request->status == 'refused' && !$model->refused_at) {
            $model->refused_at = Carbon::now()->toDateTimeString();
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function files()
    {
        return $this->hasMany(EntryFile::class, 'entry_id', 'id')
            ->whereNotNull('filename');
    }

    public function reviews()
    {
        return $this->hasMany(EntryReview::class, 'entry_id', 'id');
    }
}
