<?php

namespace App\Models;

use App\Http\Requests\ReportRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Report extends Model
{

    use SoftDeletes, LogsActivity, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['status'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'month',
        'filename',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];


    protected static $logAttributes = [
        'file',
        'published_at'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;


    public function getAll(ReportRequest $request)
    {
        $items = Report::query()
            ->when(true, function ($query)  use ($request) {
                $request->makeOrderBy($query, 'reports');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%");
            })
            ->when($request->status == 'visible', function ($query) {
                $query->whereNotNull('published_at');
                $query->orderBy('year', 'desc');
            })
            ->when($request->status == 'hidden', function ($query) {
                $query->whereNull('published_at');
            });

        if ($request->full) {
            $items = $items->get();
        } else {
            $items = $items->paginate();
        }

        return $items;
    }

    public function saveFile($file)
    {
        $y = date('Y');
        $m = date('m');
        $name = Str::random(10);

        $path = "reports/{$y}/{$m}/{$name}.pdf";

        Storage::disk('public')->put($path, base64_decode(explode(',', $file)[1]));

        return $path;
    }
}
