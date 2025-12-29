<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ApiResponse;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return ApiResponse::error('Credenciales incorrectas', 401);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
        ], 'Login exitoso');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logout exitoso');
    }
}
