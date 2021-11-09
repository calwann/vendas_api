<?php

namespace App\Http\Middleware;

use App\Http\Services\AuthService;
use App\Models\AccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccessTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $validator = AuthService::validateToken($request);
        if (!$validator->passes()) return new JsonResponse(['errors' => $validator->errors()->all()], 401);

        $user = AuthService::confirmToken($request);
        if (!$user) return new JsonResponse(['errors' => 'Invalid token'], 401);

        return $next($request);
    }
}
