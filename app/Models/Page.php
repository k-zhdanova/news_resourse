<?php

namespace App\Models;

use App\Http\Requests\PageRequest;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\FileHelper;

class Page extends Model implements TranslatableContract
{

    use HasFactory, Translatable, SoftDeletes, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['uri', 'image'];

    public $fillable = ['category_id'];

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
        'filename3',
    ];

    protected static $logAttributes = [
        'image',
        'uri',
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    const IMAGE_CONFIG = [
        'width'  => 576,
        'height' => 576,
        'crop'   => false
    ];

    public function getAll(PageRequest $request)
    {
        $items = Page::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'pages', 'page_translations');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%");
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->with('category')
            ->paginate();

        return $items;
    }


    public function saveFile($file)
    {
        $name = Str::random(10);
        $filename = null;

        $file_data = explode(',', $file)[1] ?? '';
        $file_data = $file_data ? base64_decode($file_data) : '';

        if ($file_data && FileHelper::checkMimeType($file_data, 'application/pdf')) {
            $filename = "{$name}.pdf";
            $path = config('custom.page_files_path') . '/' . $filename;
            Storage::disk('public')->put($path, $file_data);
        }

        return $filename;
    }

    public function category()
    {
        return $this->belongsTo(PageCategory::class);
    }
}
