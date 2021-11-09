<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Services\AuthService;

class LoginApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login API Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the API connection.
    |
    */

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = AuthService::validateLogin($request);
        if (!$validator->passes()) return new JsonResponse(['errors' => $validator->errors()->all()], 401);

        $user = AuthService::confirmLogin($request);
        if (!$user) return new JsonResponse(['errors' => ['Invalid credentials']], 401);

        $token = AuthService::generateToken($user);

        return new JsonResponse(['token' => $token], 200);
    }
}
