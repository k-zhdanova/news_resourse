<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group Users
 *
 * API для работы с пользователями админ панели
 */

class UserController extends Controller
{
    /**
     * List of users
     *
     * @authenticated
     * @param  Request  $request
     * @queryParam page integer Номер страницы с результатами выдачи
     * @queryParam sort string Поле для сортировки. По-умолчанию 'id|desc'
     * @queryParam search string Строка, которая должна содержаться в результатах выдачи
     * @queryParam role_id string IDs ролей пользователей через запятую (для получения списка пользователей по роли)
     * @return Response
     */

    public function index(Request $request)
    {
        $user  = new User();
        $users = $user->getAll($request);
        return response()->json($users);
    }


    /**
     * Create a user
     *
     * @authenticated
     * @param  Request  $request
     * @bodyParam firstname string required Имя
     * @bodyParam lastname string required Фамилия
     * @bodyParam email string required Email
     * @bodyParam password string required Пароль
     * @bodyParam password_confirmation string required Пароль підтвердження
     * @bodyParam role_id integer[] optional Массив IDs ролей пользователей
     * @return Response
     */
    public function create(Request $request)
    {
        $this->validate($request, User::getValidationRules());

        $user = new User($request->all());
        $user->password = Hash::make($request->password);
        $user->save();

        $user->setRoles($request);

        return response()->json($user, 201);
    }

    /**
     * Get specified user
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @return Response
     */
    public function show(Request $request, $id = null)
    {
        try {
            if ($id) {
                $user = User::with(['roles'])->findOrFail($id);
            } else {
                if ($request->user()) {
                    $user = User::with(['roles'])->find($request->user()->id);
                } else {
                    return response()->json([
                        'status'  => 'error',
                        'message' => __('http.unauthorized')
                    ], 401);
                }
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        return response()->json($user);
    }

    /**
     * Update specified user
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @bodyParam firstname string  Имя
     * @bodyParam lastname string  Фамилия
     * @bodyParam email string  Email
     * @bodyParam password string  Пароль
     * @bodyParam password_confirmation string required Пароль підтвердження
     * @bodyParam role_id integer[] Массив IDs ролей пользователей
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $this->validate($request, User::getValidationRules($user->id));

        $user->fill($request->all());

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->setRoles($request);

        return response()->json($user);
    }

    /**
     * Delete specified user
     *
     * @authenticated
     * @param  Request  $request
     * @urlParam id integer required
     * @return Response
     */
    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.not_found')
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => __('http.removed')
        ]);
    }

    /**
     * Get token for user
     *
     * @param  Request  $request
     * @bodyParam email string required Email
     * @bodyParam password string required Пароль
     * @return Response
     */
    public function token(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => implode(",", $validator->messages()->all())
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.incorrect_credentials')
            ], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.incorrect_credentials')
            ], 422);
        }

        return response()->json([
            'token'  => $user->createToken($user->id)->plainTextToken
        ], 201);
    }

    /**
     * Login user
     *
     * @param  Request  $request
     * @bodyParam email string Email
     * @bodyParam password string Пароль
     * @return Response
     */
    public function login(Request $request)
    {

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            return response()->json([
                'user'  => $user,
                'token' => $user->createToken($user->id)->plainTextToken
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => __('http.incorrect_credentials')
        ], 422);
    }

    /**
     * Logout user
     *
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->where('tokenable_id', $user->id)->delete();

            return response()->json([
                'status'  => 'ok',
                'message' => __('http.removed')
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => __('http.unauthorized')
            ], 401);
        }
    }
}
