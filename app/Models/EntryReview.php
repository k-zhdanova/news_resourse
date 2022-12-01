<?php

namespace App\Models;

use App\Http\Requests\EntryReviewRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EntryReview extends Model
{

    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entry_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'entry_id',
        'text'
    ];


    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->setCreatedAt($model->freshTimestamp());
        });
    }

    public function getAll(EntryReviewRequest $request)
    {
        $items = EntryReview::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'entry_reviews');
            })

            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('user', function ($query) use ($request) {
                        $query->where('firstname', 'LIKE', "%{$request->search}%");
                        $query->orWhere('lastname', 'LIKE', "%{$request->search}%");
                    });
                    $query->orWhereHas('entry', function ($query) use ($request) {
                        $query->where('phone', 'LIKE', "%{$request->search}%");
                    });
                });
            })
            ->when($request->entry_id, function ($query) use ($request) {
                $query->where('entry_id', $request->entry_id);
            })->get();

        return $items;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
