<?php

namespace App\Models;

use \App\Models\RoleModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

use Spatie\Activitylog\Traits\LogsActivity;

class Role extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];


    protected static $logAttributes = ['name'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    /**
     * Default ROLES
     *
     * @var integer
     */
    const SUPER_ADMIN_ROLE = 1;

    public function getOrderBy(Request $request)
    {
        $order_by = $request->input('sort');
        if ($order_by) {

            $order_by = explode('|', $order_by);
            $columns  =  Schema::getColumnListing($this->table);

            if (!empty($order_by[0]) && in_array($order_by[0], $columns)) {
                $order_by[0] = $order_by[0];
            } else {
                $order_by[0] = 'name';
            }

            if (!empty($order_by[1]) && in_array($order_by[1], ['asc', 'desc'])) {
                $order_by[1] = $order_by[1];
            } else {
                $order_by[1] = 'asc';
            }
        }
        return $order_by;
    }

    public function getAll(Request $request)
    {
        $order_by = $this->getOrderBy($request);

        $roles = Role::query()
            ->select('*')
            //            ->addSelect(\DB::raw('IF(id > ' . Role::SUPER_ADMIN_ROLE . ', true, false) as is_deletable'))
            ->when($order_by, function ($query) use ($order_by) {
                $query->orderBy($order_by[0], $order_by[1]);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->search}%");
                $query->where('id', '!=', Role::SUPER_ADMIN_ROLE);
            })
            ->when($request->user_id, function ($query) use ($request) {
                $query->whereHas('users', function ($query) use ($request) {
                    $query->where("user_id", $request->user_id);
                });
                $query->with('modules');
            })
            ->when($request->user(), function ($query) use ($request) {
                $min_role = $request->user()->roles()->min('role_id');
                $query->where('id', '>=', $min_role);
            })
            ->paginate();
        return $roles;
    }

    static function getValidationRules($id = null)
    {
        return [
            'name' => $id ? 'max:50' : 'required|max:50'
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_roles_rel', 'role_id', 'user_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'roles_modules_rel', 'role_id', 'module_id')
            ->withPivot('scopes')
            ->with('parent');
    }

    public function setModules(Request $request)
    {

        $module_ids = [];

        if ($request->modules) {

            $new_and_exist_ids = [];

            foreach ($request->modules as $key => $value) {
                array_push($new_and_exist_ids, $value);
            }

            RoleModule::where('role_id', $this->id)
                ->whereNotIn('module_id', $new_and_exist_ids)
                ->delete();

            foreach ($request->modules as $key => $value) {

                $scopes = RoleModule::DEFAULT_SCOPES;
                if (array_key_exists($key, $request->scopes)) {
                    $scopes = json_decode(json_encode($request->scopes[$key]));
                }

                try {
                    RoleModule::updateOrCreate(
                        ['role_id' => $this->id, 'module_id' => $value * 1],
                        ['scopes' => $scopes]
                    );
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
    }
}
