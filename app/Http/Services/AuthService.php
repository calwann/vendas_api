<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\AccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthService extends Service
{
    /**
     * Generate token for authenticate user.
     *
     * @param  User  $userId
     * @return mixed
     */
    public static function generateToken(User $user)
    {
        $accessToken = AccessToken::where('user_id', $user->id)->first();

        if ($accessToken) {
            $accessToken->token = hash('sha256', Str::random(60));
            $accessToken->save();
        } else {
            $accessToken = new AccessToken;
            $accessToken->user_id = $user->id;
            $accessToken->token = hash('sha256', Str::random(60));
            $accessToken->save();
        }

        return $accessToken->token;
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public static function validateLogin(Request $request)
    {
        return Validator::make($request->json()->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);
    }

    /**
     * Validate the user token middleware request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public static function validateToken(Request $request)
    {
        return Validator::make(['token' => $request->header('x-req')], [
            'token' => ['required', 'string', 'max:80']
        ]);
    }

    /**
     * Confirm email and password from request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public static function confirmLogin(Request $request)
    {
        $data = $request->json()->all();

        if (Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => 'Active'
        ])) return User::where('id', Auth::id())->first();

        return null;
    }

    /**
     * Confirm token middleware from request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public static function confirmToken(Request $request)
    {
        $token = $request->header('x-req');

        $accessToken = AccessToken::where('token', $token)
            ->whereRaw('updated_at >= DATE_sub(NOW(), INTERVAL 2 HOUR)')
            ->first();

        if ($accessToken) {
            $accessToken->updated_at = self::getNow();
            $accessToken->save();

            $user = User::where('id', $accessToken->user_id)->first();

            if ($user) {
                Auth::login($user);
                return $user;
            }
        }

        return null;
    }
}
