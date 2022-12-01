<?php

namespace App\Models;

use App\Http\Requests\InstitutionRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

class Institution extends Model
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

    protected $casts = [
        'phones' => 'array',
        'emails' => 'array'
    ];

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'meta_title',
        'meta_description',
        'address',
        'schedule'
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

    public function getAll(InstitutionRequest $request)
    {
        $items = Institution::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'institutions', 'institution_translations');
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

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'institution_id', 'id');
    }

    public static function findOrCreateNewInstance($id)
    {
        $institution = Institution::find($id);

        if (!$institution || empty($institution)) {
            $new_institution = new Institution();
            $new_institution->uri = Str::slug($id, '-');

            $new_institution->fill([
                'uk' => [
                    'institution_id' => $new_institution->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'institution_id' => $new_institution->id,
                    'locale' => 'uk',
                    'name' => $id,
                ]
            ]);

            $new_institution->save();

            return $new_institution->id;
        } else {
            return $institution->id;
        }
    }
}
