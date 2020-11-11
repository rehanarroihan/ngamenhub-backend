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
                'message' => 'Bearer not provided',
                'data' => null
            ], 401);
        }
        $token = explode(' ', $token);

        if (($token[0] !== 'Bearer') || !$token[1]) {
            return response()->json([
                'status' => false,
                'message' => 'Bearer not provided',
                'data' => null
            ], 401);
        }

        try {
            $credentials = JWT::decode($token[1], env('JWT_KEY'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Auth token expired'
            ], 400);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error with auth token',
                'data' => null
            ], 400);
        }

        $users = User::where('email', $credentials->sub->email)->first();

        if (!$users) {
            return response()->json([
                'status' => false,
                'message' => 'User with bearer not match',
                'data' => null
            ], 400);
        }

        $request->user = $credentials->sub;
        return $next($request);
    }
}
