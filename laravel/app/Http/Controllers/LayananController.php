<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LayananController extends Controller
{
    // Menampilkan layanan aktif
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Layanan::where('is_active', 1)
                ->orderBy('id_layanan', 'desc')
                ->get()
        ]);
    }

    // Menampilkan detail layanan
    public function show($id)
    {
        $layanan = Layanan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }

    // Menambahkan layanan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:100',
            'jenis_satuan' => 'required|in:KG,PCS',
            'harga_per_satuan' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data layanan tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'jenis_satuan' => $request->jenis_satuan,
            'harga_per_satuan' => $request->harga_per_satuan,
            'is_active' => 1,
            'id_dibuat_oleh' => $user->id_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan.',
            'data' => $layanan
        ], 201);
    }

    // Menonaktifkan layanan
    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);

        if ($layanan->detailTransaksi()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan sudah digunakan dalam transaksi sehingga tidak dapat dihapus.'
            ], 422);
        }

        $layanan->update([
            'is_active' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dinonaktifkan.'
        ]);
    }
}