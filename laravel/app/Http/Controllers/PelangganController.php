<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}