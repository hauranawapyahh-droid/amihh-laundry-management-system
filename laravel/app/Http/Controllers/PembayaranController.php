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
            $pembayaran = Pembayaran::where('id_pembayaran', $id)
                ->lockForUpdate()
                ->first();

            if (!$pembayaran) {
                return ['error' => 'Pembayaran tidak ditemukan.', 'code' => 404];
            }

            if ($pembayaran->metode_pembayaran === 'TUNAI') {
                return ['error' => 'Pembayaran tunai diproses melalui kasir.', 'code' => 422];
            }

            if ($pembayaran->status_konfirmasi !== 'PENDING') {
                return ['error' => 'Pembayaran sudah diproses.', 'code' => 422];
            }

            $transaksi = Transaksi::where('id_transaksi', $pembayaran->id_transaksi)
                ->lockForUpdate()
                ->first();

            if (!$transaksi || $transaksi->status_pembayaran === 'LUNAS') {
                return ['error' => 'Transaksi tidak valid atau sudah lunas.', 'code' => 422];
            }

            $noTelepon = DB::table('pelanggan')
                ->where('id_pelanggan', $transaksi->id_pelanggan)
                ->value('no_telepon');

            $isPelangganUmum = str_starts_with((string) $noTelepon, 'UMUM-');

            if (!$isPelangganUmum && !$pembayaran->bukti_pembayaran) {
                return ['error' => 'Bukti pembayaran belum tersedia.', 'code' => 422];
            }

            $pembayaran->status_konfirmasi = 'CONFIRMED';
            $pembayaran->id_konfirmator = $user->id_user;
            $pembayaran->waktu_konfirmasi = now();
            $pembayaran->catatan_pelunasan = $request->input(
                'catatan_pelunasan',
                'Pembayaran diverifikasi kasir.'
            );
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

    public function unggahBuktiKasir(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $hasil = DB::transaction(function () use ($request, $id) {
            $pembayaran = Pembayaran::where('id_pembayaran', $id)
                ->lockForUpdate()
                ->first();

            if (!$pembayaran) {
                return ['error' => 'Pembayaran tidak ditemukan.', 'code' => 404];
            }

            if (!in_array($pembayaran->metode_pembayaran, ['QRIS', 'TRANSFER_BANK'])) {
                return ['error' => 'Upload bukti hanya untuk QRIS atau transfer bank.', 'code' => 422];
            }

            if ($pembayaran->status_konfirmasi !== 'PENDING') {
                return ['error' => 'Pembayaran sudah diproses.', 'code' => 422];
            }

            if ($pembayaran->bukti_pembayaran) {
                return ['error' => 'Bukti pembayaran sudah tersedia.', 'code' => 422];
            }

            $transaksi = Transaksi::where('id_transaksi', $pembayaran->id_transaksi)
                ->lockForUpdate()
                ->first();

            if (!$transaksi || $transaksi->status_pembayaran === 'LUNAS') {
                return ['error' => 'Transaksi tidak valid atau sudah lunas.', 'code' => 422];
            }

            $path = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

            if (!$path) {
                return ['error' => 'Gagal menyimpan bukti pembayaran.', 'code' => 500];
            }

            $pembayaran->bukti_pembayaran = $path;
            $pembayaran->catatan_pelunasan = 'Bukti diunggah oleh kasir. Menunggu verifikasi.';
            $pembayaran->id_konfirmator = null;
            $pembayaran->waktu_konfirmasi = null;
            $pembayaran->save();

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
            'message' => 'Bukti berhasil diunggah. Silakan periksa sebelum verifikasi.',
        ]);
    }

    public function tolak(Request $request, $id)
    {
        $request->validate([
            'catatan_pelunasan' => 'nullable|string|max:1000',
        ]);

        $pembayaran = Pembayaran::where('id_pembayaran', $id)->first();

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan.',
            ], 404);
        }

        if ($pembayaran->status_konfirmasi !== 'PENDING' || !$pembayaran->bukti_pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti tidak tersedia atau sudah diproses.',
            ], 422);
        }

        $pembayaran->status_konfirmasi = 'REJECTED';
        $pembayaran->catatan_pelunasan = $request->input(
            'catatan_pelunasan',
            'Bukti pembayaran ditolak kasir. Silakan unggah ulang.'
        );
        $pembayaran->save();

        return response()->json([
            'success' => true,
            'message' => 'Bukti ditolak. Pelanggan dapat mengunggah bukti pembayaran kembali.',
        ]);
    }

    public function menunggu()
    {
        $data = DB::table('pembayaran as p')
            ->join('transaksi as t', 't.id_transaksi', '=', 'p.id_transaksi')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 't.id_pelanggan')
            ->where('p.status_konfirmasi', 'PENDING')
            ->select(
                'p.id_pembayaran',
                'p.metode_pembayaran',
                'p.bukti_pembayaran',
                't.kode_transaksi',
                't.total_harga',
                'pl.nama as nama_pelanggan',
                'pl.no_telepon'
            )
            ->orderByDesc('p.id_pembayaran')
            ->get()
            ->map(function ($item) {
                $item->url_bukti = $item->bukti_pembayaran
                    ? asset('storage/' . $item->bukti_pembayaran)
                    : null;

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
