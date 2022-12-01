<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

/**
 * @group Roles
 *
 * API для работы с ролями пользователей админ панели
 */

class RoleController extends Controller
{
    /**
     * List of roles
     *
     * @authenticated
     * @param  Request  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию  'name|аsc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam user_id integer ID пользователя (для просмотра всех ролей пользователя)
     * @return Response
     */

    public function index(Request $request)
    {
        $role  = new Role();
        $roles = $role->getAll($request);
        return response()->json($roles);
    }

    /**
     * Create a role
     *
     * @authenticated
     * @param  Request  $request
     * @bodyParam name string required Название роли (максимальная длина 50 символов)
     * @bodyParam module_id integer optional Массив IDs модулей админ-панели
     * @return Response
     */
    public function create(Request $request)
    {
        $this->validate($request, Role::getValidationRules());
            
        $role = new Role($request->all());
        $role->save();

        $role->setModules($request);

        return response()->json($role, 201);
    }

    /**
     * Get specified role
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @return Response
     */
    public function show($id)
    {
        try {
            $role = Role::with('modules')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($role);
    }

    /**
     * Update specified role
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @bodyParam name string Название роли
     * @bodyParam module_id integer optional Массив IDs модулей админ-панели
     * @return Response
     */
    public function update(Request $request, $id)
    {        
        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }
        
        $this->validate($request, Role::getValidationRules($role->id));

        $role->fill($request->all());
        $role->save();

        $role->setModules($request);

        return response()->json($role);
    }

    /**
     * Delete specified role
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @return Response
     */
    public function delete($id)
    {
        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $role->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }
}
