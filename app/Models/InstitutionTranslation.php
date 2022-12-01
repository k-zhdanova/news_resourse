<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class InstitutionTranslation extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'meta_title',
        'meta_description',
        'address',
        'schedule'
    ];

    protected static $logAttributes = [
        'name',
        'meta_title',
        'meta_description',
        'address',
        'schedule'
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    /**
     * Disable timestamps
     *
     * @var array
     */
    public $timestamps = false;
}
