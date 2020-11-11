<?php

namespace App\Http\Middleware;

use Closure;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = \App\Token::where('token', $request->header('Authorization'))->first();

        if (!$token) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1004",
                "errors" => null,
            ], 201);
        }

        return $next($request);
    }
}
