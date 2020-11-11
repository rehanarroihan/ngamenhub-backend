<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct() {

    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
			'email' => 'email|required|unique:users,email',
			'password' => 'required',
			'role' => 'required',
			'phone' => 'required',
			'full_name' => 'required'
		]);

		if ($validator->fails()) {
            if ($validator->messages('email')->first() == 'The email has already been taken.') {
                return ResponseFormatter::error(
                    null,
                    'Email already registered',
                    500
                );
            }

            return ResponseFormatter::validatorFailed();
        }

        try {
            User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'role' => $request->role,
                'phone' => $request->phone,
                'password' => md5($request->password),
            ]);
    
            $user = User::where('email', $request->email)->first();
            
            return ResponseFormatter::success(
                $user,
                'User registration successful'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                null,
                $error,
                500
            );
        }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
		]);
		
		if ($validator->fails()) {
			return ResponseFormatter::validatorFailed();
        }
        
        $userRegistered = User::where([
			'email' => $request->email,
			'password' => md5($request->password)
		])->first();
		if (!$userRegistered) {
			return ResponseFormatter::error(
                null,
                'Invalid email or password',
                401
            );
        }

        return ResponseFormatter::success(
            [
                'detail' => $userRegistered,
                'token' => $this->jwt($userRegistered)
            ],
            'Login successful'
        );
    }

    public function forgot(Request $request) {

    }

    private function jwt(User $user) {
        $payload = [
            'iss'   => 'ngamenhub-api',
            'sub'   => $user,
            'iat'   => time(),
            'exp'   => time() + (24 * 60 * 60 * 7)
        ];
        return JWT::encode($payload, env('JWT_KEY'));
    }
}
