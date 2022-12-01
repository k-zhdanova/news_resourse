<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'email',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'deleted_at'
    ];

    protected static $logAttributes = ['firstname', 'lastname', 'email', 'password'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;


    public function getOrderBy(Request $request)
    {
        $order_by = $request->input('sort');
        if ($order_by) {

            $order_by = explode('|', $order_by);
            $columns  = Schema::getColumnListing($this->table);

            if (!empty($order_by[0]) && in_array($order_by[0], $columns)) {
                $order_by[0] = $order_by[0];
            } else {
                $order_by[0] = 'users.id';
            }

            if (!empty($order_by[1]) && in_array($order_by[1], ['asc', 'desc'])) {
                $order_by[1] = $order_by[1];
            } else {
                $order_by[1] = 'desc';
            }
        }
        return $order_by;
    }

    public function getAll(Request $request)
    {
        $order_by   = $this->getOrderBy($request);

        $users = $this->query()->select('users.*')
            ->when($order_by, function ($query) use ($order_by) {
                $query->orderBy($order_by[0], $order_by[1]);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('firstname', 'LIKE', "%$request->search%");
                    $query->orWhere('lastname', 'LIKE', "%$request->search%");
                    $query->orWhereHas('roles', function ($query) use ($request) {
                        $query->where('name', 'LIKE', "%{$request->search}%");
                    });
                });
            })
            ->with('roles')
            ->leftJoin('users_roles_rel', function ($join) {
                $join->on('users.id', '=', 'users_roles_rel.user_id')
                    ->where('users_roles_rel.role_id', Role::SUPER_ADMIN_ROLE);
            })
            ->when($request->role_id, function ($query) use ($request) {
                $query->whereHas('roles', function ($query) use ($request) {
                    $query->where('role_id', $request->role_id);
                });
            })
            ->paginate();
        return $users;
    }

    static function getValidationRules($id = null)
    {
        return [
            'role_id.*'  => 'exists:roles,id',
            'firstname'  => $id ? 'max:50' : 'required|max:50',
            'lastname'   => 'max:50',
            'email'      => $id ?
                'email|unique:users,email,' . $id . ',id,deleted_at,NULL' :
                'required|email|unique:users,email,NULL,deleted_at',
            'password'   => $id ? 'nullable|confirmed|min:6' : 'required|confirmed|min:6'
        ];
    }

    public function entries()
    {
        return $this->hasMany(Entry::class, 'user_id')
            ->with(['files', 'reviews']);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles_rel', 'user_id', 'role_id');
    }

    public function setRoles(Request $request)
    {

        $role_ids = [UserRole::DEFAULT_ROLE];

        if ($request->role_id) {
            UserRole::where('user_id', $this->id)->delete();

            $role_ids = $request->role_id;
            if (!is_array($role_ids)) {
                $role_ids = [$role_ids];
            }
        }

        foreach ($role_ids as $role_id) {
            UserRole::create([
                'user_id' => $this->id,
                'role_id' => $role_id
            ]);
        }
    }
}
