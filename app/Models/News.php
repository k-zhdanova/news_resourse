<?php

namespace App\Models;

use App\Http\Requests\NewsRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;

class News extends Model implements TranslatableContract
{

    use HasFactory, Translatable, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['uri', 'status', 'image'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'publish_date',
        'publish_time',
        'is_delayed'
    ];

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'text',
        'meta_title',
        'meta_description'
    ];

    protected static $logAttributes = [
        'uri',
        'image',
        'published_at'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    const IMAGE_CONFIG = [
        'width'  => 576,
        'height' => 576,
        'crop'   => false
    ];

    public function getAll(NewsRequest $request)
    {
        $items = News::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'news', 'news_translations');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%");
            })
            ->when($request->status == 'visible', function ($query) {
                $query->whereNotNull('published_at');
            })
            ->when($request->status == 'hidden', function ($query) {
                $query->whereNull('published_at');
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->is_archive == '0', function ($query) use ($request) {
                $query->where('is_archive', 0);
            })
            ->when($request->is_archive == '1', function ($query) use ($request) {
                $query->where('is_archive', 1);
            })
            ->with('category')
            ->paginate();

        return $items;
    }

    public function getPublishDateAttribute()
    {
        if (!$this->published_at) {
            return null;
        }
        return Carbon::parse($this->published_at)->setTimezone('Europe/Kiev')->format('Y-m-d');
    }

    public function getPublishTimeAttribute()
    {
        if (!$this->published_at) {
            return null;
        }
        return Carbon::parse($this->published_at)->setTimezone('Europe/Kiev')->format('H:i');
    }

    public function getIsDelayedAttribute()
    {
        if (!$this->published_at) {
            return false;
        }
        return Carbon::now()->diffInSeconds(Carbon::parse($this->published_at), false) > 0;
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class);
    }
}
