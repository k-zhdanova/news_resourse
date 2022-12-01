<?php

namespace App\Models;

use App\Http\Requests\EntryFileRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;

class EntryFile extends Model
{

    use HasFactory, LogsActivity;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entry_id',
        'files',
        'filename',
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->setCreatedAt($model->freshTimestamp());
        });
    }

    public function getAll(EntryFileRequest $request)
    {
        $items = EntryFile::query()
            ->when(true, function ($query) use ($request) {
                $request->makeOrderBy($query, 'entryfiles');
            })
            ->when($request->entry_id, function ($query) use ($request) {
                $query->whereHas('entry', function ($query) use ($request) {
                    $query->where('entry_id', $request->entry_id);
                });
            })
            ->with('entry')
            ->paginate();
        return $items;
    }

    public function saveFile($request, $id)
    {
        $y = date('Y');
        $m = date('m');
        $d = date('d');

        $path = "entries/{$y}/{$m}/{$d}";

        $results = [];

        $files = $request->file('files');
        foreach ($files as $file) {
            $entry_file = new EntryFile();
            $entry_file->entry_id = $id;
            // $entry_file->filename = $file->store($path);
            $entry_file->filename = $path;

            Storage::disk('public')->put($path, $file);

            $entry_file->save();
            $results[] = $entry_file;
        }
        return $results;
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
