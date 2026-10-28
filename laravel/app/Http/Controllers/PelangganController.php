<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DATA PELANGGAN
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL PELANGGAN
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TAMBAH PELANGGAN
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'no_telepon' => [
                'required',
                'string',
                'max:30',
                'unique:pelanggan,no_telepon',
            ],

            'alamat' => [
                'nullable',
                'string',
            ],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',

            'no_telepon.required' =>
                'Nomor telepon wajib diisi.',

            'no_telepon.unique' =>
                'Nomor telepon sudah digunakan. Gunakan nomor telepon lain.',
        ]);


        $pelanggan = Pelanggan::create([
            'nama' => $validated['nama'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'] ?? null,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'data' => $pelanggan,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | PELANGGAN UMUM UNTUK TRANSAKSI KASIR
    |--------------------------------------------------------------------------
    */

    public function pelangganUmum()
    {
        $nomor = 'UMUM-' . strtoupper(\Illuminate\Support\Str::random(15));

        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Umum',
            'no_telepon' => $nomor,
            'alamat' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan umum berhasil disiapkan.',
            'data' => $pelanggan,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT PELANGGAN
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | HAPUS PELANGGAN
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        try {
            $pelanggan->delete();
        } catch (QueryException $e) {
            // Biasanya karena pelanggan sudah punya transaksi (foreign key)
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