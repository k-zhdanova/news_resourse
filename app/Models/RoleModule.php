<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

use Spatie\Activitylog\Traits\LogsActivity;

class RoleModule extends Pivot
{
    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles_modules_rel';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['module_id', 'role_id', 'scopes'];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $casts = [
        'scopes' => 'array'
    ];

    /**
     * Default Scopes
     *
     * @var integer
     */
    const DEFAULT_SCOPES = [
        'read'   => "1",
        'create' => "0",
        'update' => "0",
        'delete' => "0"
    ];

    static function getValidationRules() 
    {
        return [
            'module_id' => 'required|exists:modules,id',
            'role_id'   => 'required|exists:roles,id',
        ];
    }
}
