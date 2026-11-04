<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PelangganController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Pelanggan::orderBy('id_pelanggan', 'desc')->get(),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => Pelanggan::findOrFail($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:30|unique:pelanggan,no_telepon',
            'alamat' => 'nullable|string|max:255',
        ]);

        $pelanggan = DB::transaction(function () use ($validated) {
            $nama = trim($validated['nama']);
            $dasar = Str::slug($nama, '');
            $dasar = $dasar !== '' ? strtolower($dasar) : 'pelanggan';
            $username = $dasar;

            if (User::where('username', $username)->exists()) {
                $ekor = preg_replace('/\D/', '', $validated['no_telepon']);
                $username = $dasar . substr($ekor, -4);

                $nomor = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $dasar . substr($ekor, -4) . $nomor;
                    $nomor++;
                }
            }

            $user = User::create([
                'nama' => $nama,
                'username' => $username,
                'password_hash' => Hash::make($validated['no_telepon']),
                'role' => 'CUSTOMER',
                'is_active' => 1,
            ]);

            return Pelanggan::create([
                'id_user' => $user->id_user,
                'nama' => $nama,
                'no_telepon' => $validated['no_telepon'],
                'alamat' => $validated['alamat'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan dan akun login berhasil dibuat.',
            'data' => $pelanggan,
        ], 201);
    }

    public function pelangganUmum()
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Umum',
            'no_telepon' => 'UMUM-' . strtoupper(Str::random(15)),
            'alamat' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan umum berhasil disiapkan.',
            'data' => $pelanggan,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:30|unique:pelanggan,no_telepon,' . $id . ',id_pelanggan',
            'alamat' => 'nullable|string',
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.unique' => 'Nomor telepon sudah digunakan. Gunakan nomor telepon lain.',
        ]);

        $pelanggan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data' => $pelanggan,
        ]);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        try {
            $pelanggan->delete();
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak bisa dihapus karena sudah memiliki transaksi.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}