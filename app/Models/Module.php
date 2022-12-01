<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Module extends Model
{
    use HasFactory;

    /**
     * Disable timestamps
     *
     * @var array
     */
    public $timestamps = false;

    protected $casts = [
        'models' => 'array'
    ];

    public function getAll(Request $request)
    {
        $limited = false;
        $role_ids = [];
        
        if ($request->user()) {
            $role_ids = $request->user()
                           ->roles
                           ->pluck('id')
                           ->toArray();
            if (!in_array(Role::SUPER_ADMIN_ROLE, $role_ids)) {
                $limited = true;
            }
        }

        $items = Module::query()
            ->orderBy('sort', 'asc')
            ->when($limited, function($query) use ($role_ids) {
                $query->whereHas('roles', function($query) use ($role_ids) {
                    $query->whereIn('roles.id', $role_ids);
                });
            })
            ->when($request->role_id, function($query) use ($request) {
                $query->with('roles');
            })
            ->with('parent')
            ->get();

        if ($request->role_id) {
            $items = $items->map(function($item) use ($request) {

                if ($item->plural && !$item->singular) {
                    $item->is_parent = 1;
                }

                $item->checked = 0;

                foreach ($item->roles as $role) {
                    if ($role->id == $request->role_id && $role->pivot) {
                        $item->scopes = json_decode($role->pivot->scopes, 1);
                    }
                    if (!$item->checked && $role->pivot->role_id == $request->role_id) {
                        $item->checked = 1;
                    }
                }
                unset($item->roles);
                return $item;
            
            });
        }

        return $items;
    }

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function getParentAttribute($value) 
    {
        return $this->hasOne(Module::class);
    }

    public function childs()
    {
        return $this->hasMany(Module::class, 'parent_id', 'id')
                    ->orderBy('sort', 'asc');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_modules_rel', 'module_id', 'role_id')
                    ->withPivot('scopes');
    }
}
