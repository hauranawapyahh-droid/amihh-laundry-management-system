<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\BiayaOperasional;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function totalPendapatan()
    {
        return (float) Transaksi::where('status_pembayaran', 'LUNAS')
            ->sum('total_harga');
    }

    private function totalBiaya()
    {
        return (float) BiayaOperasional::sum('jumlah_biaya');
    }

    public function pendapatan()
    {
        $transaksiLunas = Transaksi::where('status_pembayaran', 'LUNAS');

        return response()->json([
            'success' => true,
            'data' => [
                'total_pendapatan' => (float) $transaksiLunas->sum('total_harga'),
                'jumlah_transaksi' => $transaksiLunas->count(),
            ],
        ]);
    }

    public function biaya()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_biaya' => $this->totalBiaya(),
            ],
        ]);
    }

    public function keuntungan()
    {
        $pendapatan = $this->totalPendapatan();
        $biaya = $this->totalBiaya();

        return response()->json([
            'success' => true,
            'data' => [
                'keuntungan' => $pendapatan - $biaya,
                'total_pendapatan' => $pendapatan,
                'total_biaya' => $biaya,
            ],
        ]);
    }
}