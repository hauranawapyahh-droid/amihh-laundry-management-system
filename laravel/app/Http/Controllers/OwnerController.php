<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\BiayaOperasional;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    private function pastikanOwner(Request $request): void
    {
        abort_unless(
            $request->user() &&
            in_array($request->user()->role, ['OWNER', 'ADMIN'], true),
            403,
            'Kamu tidak memiliki akses ke fitur ini'
        );
    }

    private function omzet($query): int
    {
        return (int) (clone $query)
            ->where('status_pembayaran', 'LUNAS')
            ->sum('total_harga');
    }

    public function overview(Request $request)
    {
        $this->pastikanOwner($request);

        $now = now();

        $bulan = fn ($date) => Transaksi::whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month);

        $biayaBulan = fn ($date) => BiayaOperasional::whereYear('tanggal_biaya', $date->year)
            ->whereMonth('tanggal_biaya', $date->month);

        $tren = Transaksi::where('status_pembayaran', 'LUNAS')
            ->whereDate('created_at', '>=', $now->copy()->subDays(29))
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jumlah, SUM(total_harga) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        $bulanan = collect(range(5, 0))->map(function ($i) use ($now, $bulan, $biayaBulan) {
            $date = $now->copy()->startOfMonth()->subMonths($i);

            return [
                'bulan' => $date->translatedFormat('M Y'),
                'pendapatan' => $this->omzet($bulan($date)),
                'biaya' => (int) $biayaBulan($date)->sum('jumlah_biaya'),
            ];
        })->values();

        $aktivitasTransaksi = Transaksi::with(['pelanggan', 'kasir'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($transaksi) => [
                'judul' => 'Transaksi #' . $transaksi->kode_transaksi,
                'jumlah' => (float) $transaksi->total_harga,
                'oleh' => $transaksi->kasir?->nama ?? '-',
                'waktu' => $transaksi->created_at->diffForHumans(),
                'ts' => $transaksi->created_at,
            ]);

        $aktivitasBiaya = BiayaOperasional::with('pencatat')
            ->latest('tanggal_biaya')
            ->take(5)
            ->get()
            ->map(fn ($biaya) => [
                'judul' => 'Biaya ' . $biaya->kategori,
                'jumlah' => (float) $biaya->jumlah_biaya,
                'oleh' => $biaya->pencatat?->nama ?? '-',
                'waktu' => $biaya->created_at->diffForHumans(),
                'ts' => $biaya->created_at,
            ]);

        $aktivitas = $aktivitasTransaksi
            ->concat($aktivitasBiaya)
            ->sortByDesc('ts')
            ->take(6)
            ->map(fn ($item) => collect($item)->except('ts'))
            ->values();

        return response()->json([
            'hari_ini' => $this->omzet(Transaksi::whereDate('created_at', $now)),
            'kemarin' => $this->omzet(Transaksi::whereDate('created_at', $now->copy()->subDay())),
            'bulan_ini' => $this->omzet($bulan($now)),
            'bulan_lalu' => $this->omzet($bulan($now->copy()->subMonth())),
            'jml_transaksi_bulan' => $bulan($now)->count(),
            'biaya_bulan_ini' => (int) $biayaBulan($now)->sum('jumlah_biaya'),
            'jml_biaya' => $biayaBulan($now)->count(),
            'tren' => $tren,
            'per_layanan' => $bulan($now)
                ->where('status_pembayaran', 'LUNAS')
                ->join('detail_transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.id_transaksi')
                ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id_layanan')
                ->selectRaw('layanan.nama_layanan as layanan, SUM(detail_transaksi.subtotal) as total')
                ->groupBy('layanan.id_layanan', 'layanan.nama_layanan')
                ->orderByDesc('total')
                ->get(),
            'biaya_kategori' => $biayaBulan($now)
                ->selectRaw('kategori, SUM(jumlah_biaya) as total')
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->get(),
            'bulanan' => $bulanan,
            'aktivitas' => $aktivitas,
        ]);
    }

    public function transaksi(Request $request)
    {
        $this->pastikanOwner($request);

        $query = Transaksi::with(['pelanggan', 'kasir'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($where) use ($search) {
                $where->where('kode_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                        $pelanggan->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        return response()->json([
            'total' => $query->count(),
            'data' => $query->take(50)->get(),
        ]);
    }

    public function biaya(Request $request)
    {
        $this->pastikanOwner($request);

        return response()->json([
            'data' => BiayaOperasional::with('pencatat')
                ->latest('tanggal_biaya')
                ->take(50)
                ->get(),
        ]);
    }
}

