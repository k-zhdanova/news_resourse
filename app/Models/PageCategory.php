<?php

namespace App\Models;

use App\Http\Requests\PageCategoryRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

class PageCategory extends Model implements TranslatableContract
{

    use HasFactory, Translatable, SoftDeletes, LogsActivity;


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

    protected static $logAttributes = [];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;


    public function getAll(PageCategoryRequest $request)
    {

        $items = PageCategory::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'page_categories', 'page_category_translations');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%");
            })
            ->when($request->parent_id, function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            })
            ->when($request->exclude, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude);
            })
            ->with('parent')
            ->with('childs')
            ->paginate();

        return $items;
    }

    public function parent()
    {
        return $this->belongsTo(PageCategory::class, 'parent_id');
    }

    public function getParentAttribute($value)
    {
        return $this->hasOne(PageCategory::class);
    }

    public function childs()
    {
        return $this->hasMany(PageCategory::class, 'parent_id', 'id')
            ->orderBy('id', 'asc');
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'category_id', 'id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $category = PageCategory::find($id);

        if (!$category || empty($category)) {
            $new_category = new PageCategory();

            $new_category->fill([
                'uk' => [
                    'page_category_id' => $new_category->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'page_category_id' => $new_category->id,
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
