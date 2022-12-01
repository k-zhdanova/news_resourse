<?php

namespace App\Models;

use App\Helpers\FileHelper;
use App\Http\Requests\ServiceRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model implements TranslatableContract
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
        'text',
        'meta_title',
        'meta_description',
        'filename1',
        'filename2',
        'place',
        'term'
    ];

    protected static $logAttributes = [
        'code',
        'sector_id',
        'category_id',
        'institution_id',
        'is_online',
        'uri',
        'published_at'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getAll(ServiceRequest $request)
    {
        $items = Service::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'services', 'service_translations');
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
            ->when($request->status == 'online', function ($query) {
                $query->whereNotNull('published_at');
                $query->where('is_online', 1);
            })
            ->when($request->sector_id, function ($query) use ($request) {
                $query->where('sector_id', $request->sector_id);
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->institution_id, function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            })
            ->when($request->service_id, function ($query) use ($request) {
                $query->where('id', $request->service_id);
            })
            ->with('sector')
            ->with('category')
            ->with('institution')
            ->paginate();

        return $items;
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function queues()
    {
        return $this->hasMany(QueueService::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(FeedBack::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function getSectorAttribute($value)
    {
        return $this->hasOne(Sector::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getCategoryAttribute($value)
    {
        return $this->hasOne(Category::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function getInstitutionAttribute($value)
    {
        return $this->hasOne(Institution::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'services_tags_rel', 'service_id');
    }

    public function getTags($locale = '')
    {
        $tags = [];
        $locales = config('translatable.locales');

        if ($locale && !in_array($locale, $locales)) {
            return $tags;
        }

        $locales = $locale ? [$locale] : $locales;

        foreach ($this->tags as $tag) {

            foreach ($locales as $loc) {
                $tags[] = $tag->getAttribute("name:{$loc}");
            }
        }

        return $tags;
    }

    public function setTags(ServiceRequest $request)
    {
        $tag_ids = [];

        if ($request->tag_id) {

            $tag_ids = $request->tag_id;
            if (!is_array($tag_ids)) {
                $tag_ids = [$tag_ids];
            }
        }

        $tags_to_detele = ServiceTag::where('service_id', $this->id)
            ->whereNotIn('tag_id', $tag_ids)
            ->get();

        foreach ($tags_to_detele as $tag_to_detele) {
            $tag_to_detele->delete();
        }

        $exist_tags = ServiceTag::where('service_id', $this->id)->get();
        $exist_tags = $exist_tags->pluck('tag_id');

        foreach ($tag_ids as $tag_id) {
            if (!$exist_tags->contains($tag_id)) {
                ServiceTag::create([
                    'tag_id' => $tag_id,
                    'service_id' => $this->id,
                ]);
            }
        }
    }

    public function saveFile($file)
    {
        $name = Str::random(10);
        $filename = null;

        $file_data = explode(',', $file)[1] ?? '';
        $file_data = $file_data ? base64_decode($file_data) : '';

        if ($file_data && FileHelper::checkMimeType($file_data, 'application/pdf')) {
            $filename = "{$name}.pdf";
            $path = config('custom.service_files_path') . '/' . $filename;
            Storage::disk('public')->put($path, $file_data);
        }

        return $filename;
    }

    public static function findOrCreateNewInstance($id)
    {
        $service = Service::find($id);

        if (!$service || empty($service)) {
            $new_service = new Service();
            $new_service->uri = Str::slug($id, '-');

            $new_service->fill([
                'uk' => [
                    'service_id' => $new_service->id,
                    'locale' => 'uk',
                    'name' => $id,
                ],
                'en' => [
                    'service_id' => $new_service->id,
                    'locale' => 'uk',
                    'name' => $id,
                ]
            ]);

            $new_service->save();

            return $new_service->id;
        } else {
            return $service->id;
        }
    }
}
