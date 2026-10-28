<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PenggunaController extends Controller
{
    private const ROLES = 'ADMIN,KASIR,STAFF_LAUNDRY,OWNER,CUSTOMER';

    public function index()
    {
        $pengguna = User::select(
            'id_user',
            'nama',
            'username',
            'role',
            'is_active'
        )->orderBy('id_user')->get();

        return response()->json([
            'success' => true,
            'data' => $pengguna
        ]);
    }

    public function store(Request $request)
    {
        // Username Customer menggunakan nomor telepon.
        if ($request->input('role') === 'CUSTOMER') {
            $request->merge([
                'username' => trim((string) $request->input('no_telepon'))
            ]);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:' . self::ROLES,
            'no_telepon' => 'required_if:role,CUSTOMER|nullable|string|max:30|unique:pelanggan,no_telepon',
            'alamat' => 'required_if:role,CUSTOMER|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Akun dan data pelanggan dibuat dalam satu transaksi database.
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'password_hash' => password_hash(
                    $request->password,
                    PASSWORD_DEFAULT
                ),
                'role' => $request->role,
                'is_active' => 1,
            ]);

            if ($request->role === 'CUSTOMER') {
                Pelanggan::create([
                    'nama' => $request->nama,
                    'no_telepon' => $request->no_telepon,
                    'alamat' => $request->alamat,
                ]);
            }

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => $request->role === 'CUSTOMER'
                ? 'Akun Customer dan data pelanggan berhasil ditambahkan.'
                : 'Pengguna berhasil ditambahkan.',
            'data' => [
                'id_user' => $user->id_user,
                'nama' => $user->nama,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Edit pengguna.
     * Password hanya diganti jika field password diisi.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $id . ',id_user',
            'role' => 'required|in:' . self::ROLES,
            'is_active' => 'required|in:0,1',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();

        // Admin tidak boleh menonaktifkan atau mengubah role akunnya sendiri.
        if ((int) $currentUser->id_user === (int) $user->id_user) {
            if ((int) $request->is_active !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan'
                ], 422);
            }

            if ($request->role !== $user->role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role akun yang sedang digunakan tidak dapat diubah'
                ], 422);
            }
        }

        $user->nama = $request->nama;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->is_active = (int) $request->is_active;

        if ($request->filled('password')) {
            $user->password_hash = password_hash(
                $request->password,
                PASSWORD_DEFAULT
            );
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui',
            'data' => [
                'id_user' => $user->id_user,
                'nama' => $user->nama,
                'username' => $user->username,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]
        ]);
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

        $user->password_hash = password_hash(
            $request->password_baru,
            PASSWORD_DEFAULT
        );

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
