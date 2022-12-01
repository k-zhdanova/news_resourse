<?php

namespace App\Models;

use App\Http\Requests\CategoryRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

class Category extends Model implements TranslatableContract
{
    use HasFactory, Translatable, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['uri', 'status'];

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
        'meta_title',
        'meta_description'
    ];

    protected static $logAttributes = [
        'uri',
        'published_at'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getAll(CategoryRequest $request)
    {
        $items = Category::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'categories', 'category_translations');
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
            ->when($request->sector_id, function ($query) use ($request) {
                $query->where('sector_id', $request->sector_id);
            })
            ->with('sector')
            ->with(['services' => function ($q) {
                $q->published();
            }])
            ->paginate();

        return $items;
    }

    static function getValidationRules($id = null)
    {
        return RuleFactory::make([
            'sector_id' => $id ? 'exists:sectors,id' : 'required|exists:sectors,id',
            '%name%' => $id ? 'max:255' : 'required|max:255',
            '%meta_title%' => 'max:255',
            '%meta_description%' => 'max:255',
            'status' => 'in:0,1'
        ]);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function getSectorAttribute($value)
    {
        return $this->hasOne(Sector::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $category = Category::find($id);

        if (!$category || empty($category)) {
            $new_category = new Category();
            $new_category->uri = Str::slug($id, '-');

            $new_category->fill([
                'uk' => [
                    'category_id' => $new_category->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'category_id' => $new_category->id,
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
