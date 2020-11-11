<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Hash;
use SebastianBergmann\Timer\Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct() {

    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
			'email' => 'required', 'string', 'email', 'max:255', 'unique:users',
			'password' => 'required',
			'role' => 'required',
			'phone' => 'required',
			'full_name' => 'required'
		]);

		if ($validator->fails()) {
            return ResponseFormatter::validatorFailed();
        }

        try {
            User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'role' => $request->role,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
    
            $user = User::where('email', $request->email)->first();
            
            return ResponseFormatter::success(
                $user,
                'User registration success'
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
