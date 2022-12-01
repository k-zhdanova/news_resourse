<?php

namespace App\Models;

use App\Http\Requests\LawCategoryRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class LawCategory extends Model implements TranslatableContract
{

    use HasFactory, Translatable, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['status'];

    public $fillable = ['parent_id'];


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


    public function getAll(LawCategoryRequest $request)
    {

        $items = LawCategory::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'law_categories', 'law_category_translations');
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
            ->when($request->parent_id, function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            })
            ->when($request->exclude, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude);
            })
            ->with('parent')
            ->with(['childs' => function ($q) {
                $q->published();
            }])
            ->paginate();

        return $items;
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function parent()
    {
        return $this->belongsTo(LawCategory::class, 'parent_id');
    }

    public function getParentAttribute($value)
    {
        return $this->hasOne(LawCategory::class);
    }

    public function childs()
    {
        return $this->hasMany(LawCategory::class, 'parent_id', 'id')
            ->orderBy('id', 'asc');
    }

    public function laws()
    {
        return $this->hasMany(Law::class, 'category_id', 'id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $category = LawCategory::find($id);

        if (!$category || empty($category)) {
            $new_category = new LawCategory();

            $new_category->fill([
                'uk' => [
                    'law_category_id' => $new_category->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'law_category_id' => $new_category->id,
                    'locale' => 'uk',
                    'name' => $id,
                ]
            ]);

            $new_category->save();

            return $new_category->id;
        } else {
            return $category->id;
        }
    }
}
