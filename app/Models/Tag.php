<?php

namespace App\Models;

use App\Http\Requests\ServiceRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Http\Requests\TagRequest;

class Tag extends Model
{

    use HasFactory, Translatable, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
        'name'
    ];

    protected static $logAttributes = [];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getAll(TagRequest $request)
    {
        $items = Tag::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'tags', 'tag_translations');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%");
            })
            ->paginate();

        return $items;
    }
}
