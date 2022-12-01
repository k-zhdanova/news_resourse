<?php

namespace App\Models;

use App\Http\Requests\NewsCategoryRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class NewsCategory extends Model implements TranslatableContract
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

    public function getAll(NewsCategoryRequest $request)
    {
        $items = NewsCategory::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'news_categories', 'news_category_translations');
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
            ->paginate();

        return $items;
    }

    public function news()
    {
        return $this->hasMany(News::class, 'category_id', 'id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $category = NewsCategory::find($id);

        if (!$category || empty($category)) {
            $new_category = new NewsCategory();
            $new_category->uri = Str::slug($id, '-');

            $new_category->fill([
                'uk' => [
                    'news_category_id' => $new_category->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'news_category_id' => $new_category->id,
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
