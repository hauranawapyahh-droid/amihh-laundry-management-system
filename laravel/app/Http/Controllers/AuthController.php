<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)
            ->where('is_active', 1)
            ->first();

        if (!$user || !password_verify($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id_user' => $user->id_user,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'success' => true,
            'data' => [
                'id_user' => $user->id_user,
                'nama' => $user->nama,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
    }
}