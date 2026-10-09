<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = User::select('id_user', 'nama', 'username', 'role', 'is_active')
            ->orderBy('id_user')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pengguna
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,KASIR,STAFF_LAUNDRY,OWNER',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password_hash' => password_hash($request->password, PASSWORD_DEFAULT),
            'role' => $request->role,
            'is_active' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => [
                'id_user' => $user->id_user,
                'nama' => $user->nama,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ], 201);
    }

    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password_baru' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        $user->password_hash = password_hash($request->password_baru, PASSWORD_DEFAULT);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();

        if ((int) $currentUser->id_user === (int) $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun yang sedang digunakan tidak dapat dihapus'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ]);
    }
}