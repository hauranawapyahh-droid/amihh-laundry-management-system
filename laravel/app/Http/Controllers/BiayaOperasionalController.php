<?php

namespace App\Http\Controllers;

use App\Models\BiayaOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BiayaOperasionalController extends Controller
{
    public function index()
    {
        $data = BiayaOperasional::with('pencatat')
            ->orderBy('tanggal_biaya', 'desc')
            ->orderBy('id_biaya', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_biaya' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'jumlah_biaya' => 'required|numeric|min:0.01',
            'tanggal_biaya' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data biaya tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $biaya = BiayaOperasional::create([
            'id_pencatat' => $user->id_user,
            'nama_biaya' => $request->nama_biaya,
            'kategori' => $request->kategori,
            'jumlah_biaya' => $request->jumlah_biaya,
            'tanggal_biaya' => $request->tanggal_biaya,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Biaya berhasil ditambahkan',
            'data' => $biaya,
        ], 201);
    }

    // Mengedit biaya operasional
    public function update(Request $request, $id)
    {
        $biaya = BiayaOperasional::find($id);

        if (!$biaya) {
            return response()->json([
                'success' => false,
                'message' => 'Data biaya tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_biaya' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'jumlah_biaya' => 'required|numeric|min:0.01',
            'tanggal_biaya' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data biaya tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $biaya->update([
            'nama_biaya' => $request->nama_biaya,
            'kategori' => $request->kategori,
            'jumlah_biaya' => $request->jumlah_biaya,
            'tanggal_biaya' => $request->tanggal_biaya,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Biaya berhasil diperbarui',
            'data' => $biaya,
        ]);
    }

    public function destroy($id)
    {
        $biaya = BiayaOperasional::find($id);

        if (!$biaya) {
            return response()->json([
                'success' => false,
                'message' => 'Data biaya tidak ditemukan',
            ], 404);
        }

        $biaya->delete();

        return response()->json([
            'success' => true,
            'message' => 'Biaya berhasil dihapus',
        ]);
    }
}