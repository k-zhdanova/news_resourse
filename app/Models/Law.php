<?php

namespace App\Models;

use App\Http\Requests\LawRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Law extends Model implements TranslatableContract
{

    use Translatable, SoftDeletes, LogsActivity, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['status', 'follow'];

    public $fillable = ['link', 'category_id'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
    ];

    protected static $logAttributes = [
        'published_at'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;


    public function getAll(LawRequest $request)
    {
            $items = Law::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'laws', 'law_translations');
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
            ->with('category')
            ->paginate();

        return $items;
    }

    public function category()
    {
        return $this->belongsTo(LawCategory::class);
    }

}
