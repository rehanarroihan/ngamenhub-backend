<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => 'Bearer not provided'
            ], 401);
        }
        $token = explode(' ', $token);

        if (($token[0] !== 'Bearer') || !$token[1]) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => 'Bearer not provided'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token[1], env('JWT_KEY'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => 'Auth token expired'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => 'An error with auth token'
            ], 401);
        }

        $users = User::where('email', $credentials->sub->email)->first();

        if (!$users) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => 'User with bearer not match'
            ], 400);
        }

        $request->user = $credentials->sub;
        return $next($request);
    }
}
