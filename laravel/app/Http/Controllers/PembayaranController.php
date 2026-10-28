<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'catatan_pelunasan' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        $hasil = DB::transaction(function () use ($id, $user, $request) {
            $pembayaran = Pembayaran::where(
                'id_pembayaran',
                $id
            )->lockForUpdate()->first();

            if (!$pembayaran) {
                return ['error' => 'Pembayaran tidak ditemukan.', 'code' => 404];
            }

            if ($pembayaran->metode_pembayaran === 'TUNAI') {
                return ['error' => 'Pembayaran tunai diproses melalui kasir.', 'code' => 422];
            }

            if ($pembayaran->status_konfirmasi !== 'PENDING') {
                return ['error' => 'Pembayaran sudah diproses.', 'code' => 422];
            }

            if (!$pembayaran->bukti_pembayaran) {
                return ['error' => 'Bukti pembayaran belum tersedia.', 'code' => 422];
            }

            $transaksi = Transaksi::where(
                'id_transaksi',
                $pembayaran->id_transaksi
            )->lockForUpdate()->first();

            if (!$transaksi || $transaksi->status_pembayaran === 'LUNAS') {
                return ['error' => 'Transaksi tidak valid atau sudah lunas.', 'code' => 422];
            }

            $pembayaran->status_konfirmasi = 'CONFIRMED';
            $pembayaran->id_konfirmator = $user->id_user;
            $pembayaran->waktu_konfirmasi = now();
            $pembayaran->catatan_pelunasan =
                $request->input('catatan_pelunasan', 'Bukti pembayaran diverifikasi kasir.');
            $pembayaran->save();

            $transaksi->status_pembayaran = 'LUNAS';
            $transaksi->id_verifikator = $user->id_user;
            $transaksi->save();

            return ['success' => true];
        });

        if (isset($hasil['error'])) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], $hasil['code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi. Transaksi sekarang lunas.',
        ]);
    }
    
public function menunggu()
{
    $data = DB::table('pembayaran as p')
        ->join('transaksi as t', 't.id_transaksi', '=', 'p.id_transaksi')
        ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 't.id_pelanggan')
        ->where('p.status_konfirmasi', 'PENDING')
        ->whereNotNull('p.bukti_pembayaran')
        ->select(
            'p.id_pembayaran',
            'p.metode_pembayaran',
            'p.bukti_pembayaran',
            't.kode_transaksi',
            't.total_harga',
            'pl.nama as nama_pelanggan'
        )
        ->orderByDesc('p.id_pembayaran')
        ->get()
        ->map(function ($item) {
            $item->url_bukti = asset(
                'storage/' . $item->bukti_pembayaran
            );
            return $item;
        });

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}

}