<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LayananController extends Controller
{
    public function index()
    {
        $layanan = Layanan::orderBy('id_layanan')->get();

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }

    public function show($id)
    {
        $layanan = Layanan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:100',
            'jenis_satuan' => 'required|in:KG,PCS',
            'harga_per_satuan' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'jenis_satuan' => $request->jenis_satuan,
            'harga_per_satuan' => $request->harga_per_satuan,
            'is_active' => true,
            'id_dibuat_oleh' => $user->id_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan',
            'data' => $layanan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $layanan = Layanan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:100',
            'jenis_satuan' => 'required|in:KG,PCS',
            'harga_per_satuan' => 'required|numeric|min:0.01',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $layanan->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diperbarui',
            'data' => $layanan
        ]);
    }

    public function destroy($id)
    {
        $layanan = Layanan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak ditemukan'
            ], 404);
        }

        $layanan->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dinonaktifkan',
            'data' => $layanan
        ]);
    }
}
