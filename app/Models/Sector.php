<?php

namespace App\Models;

use App\Http\Requests\SectorRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;


class Sector extends Model implements TranslatableContract
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

    public function getAll(SectorRequest $request)
    {
        $items = Sector::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'sectors', 'sector_translations');
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
            ->with(['categories' => function ($q) {
                $q->published();
            }])
            ->with(['services' => function ($q) {
                $q->published();
            }])
            ->paginate();

        return $items;
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function categories()
    {
        return $this->hasMany(Category::class)
            ->with('services');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'sector_id', 'id')
            ->whereNull('category_id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $sector = Sector::find($id);

        if (!$sector || empty($sector)) {
            $new_sector = new Sector();
            $new_sector->uri = Str::slug($id, '-');

            $new_sector->fill([
                'uk' => [
                    'sector_id' => $new_sector->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'sector_id' => $new_sector->id,
                    'locale' => 'uk',
                    'name' => $id,
                ]
            ]);

            $new_sector->save();

            return $new_sector->id;
        } else {
            return $sector->id;
        }
    }
}
