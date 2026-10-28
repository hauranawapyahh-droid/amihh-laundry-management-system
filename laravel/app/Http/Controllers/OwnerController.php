<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Biaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Owner hanya MEMBACA data yang sudah dicatat kasir (transaksi)
 * dan admin/staff (biaya operasional). Tidak ada tabel khusus owner.
 *
 * Asumsi tabel (sesuaikan nama kolom dengan database kamu):
 *  transaksis        : kode, pelanggan, layanan, kasir, total, status('lunas'|'proses'), created_at
 *  biaya_operasionals: tanggal, kategori, keterangan, dicatat_oleh, jumlah
 */
class OwnerController extends Controller
{
    private function pastikanOwner(Request $r)
    {
        abort_unless($r->user() && $r->user()->role === 'owner', 403, 'Khusus owner');
    }

    private function omzet($q)
    {
        return (clone $q)->where('status', 'lunas')->sum('total');
    }

    public function overview(Request $r)
    {
        $this->pastikanOwner($r);
        $now = now();
        $bulan = fn($d) => Transaksi::whereYear('created_at', $d->year)->whereMonth('created_at', $d->month);
        $biayaBulan = fn($d) => Biaya::whereYear('tanggal', $d->year)->whereMonth('tanggal', $d->month);

        // tren 30 hari
        $tren = Transaksi::where('status', 'lunas')
            ->whereDate('created_at', '>=', $now->copy()->subDays(29))
            ->selectRaw('DATE(created_at) tgl, COUNT(*) jumlah, SUM(total) total')
            ->groupBy('tgl')->orderBy('tgl')->get();

        // 6 bulan terakhir
        $bulanan = collect(range(5, 0))->map(function ($i) use ($now, $bulan, $biayaBulan) {
            $d = $now->copy()->startOfMonth()->subMonths($i);
            return [
                'bulan'      => $d->translatedFormat('M Y'),
                'pendapatan' => (int) $this->omzet($bulan($d)),
                'biaya'      => (int) $biayaBulan($d)->sum('jumlah'),
            ];
        })->values();

        // aktivitas terbaru gabungan transaksi (kasir) + biaya (admin/staff)
        $aktivitas = Transaksi::latest()->take(5)->get()->map(fn($t) => [
            'judul' => 'Transaksi #' . $t->kode, 'jumlah' => $t->total,
            'oleh' => $t->kasir, 'waktu' => $t->created_at->diffForHumans(), 'ts' => $t->created_at,
        ])->concat(Biaya::latest()->take(5)->get()->map(fn($b) => [
            'judul' => 'Biaya ' . $b->kategori, 'jumlah' => $b->jumlah,
            'oleh' => $b->dicatat_oleh, 'waktu' => $b->created_at->diffForHumans(), 'ts' => $b->created_at,
        ]))->sortByDesc('ts')->take(6)->values()->map(fn($a) => collect($a)->except('ts'));

        return response()->json([
            'hari_ini'   => (int) $this->omzet(Transaksi::whereDate('created_at', $now)),
            'kemarin'    => (int) $this->omzet(Transaksi::whereDate('created_at', $now->copy()->subDay())),
            'bulan_ini'  => (int) $this->omzet($bulan($now)),
            'bulan_lalu' => (int) $this->omzet($bulan($now->copy()->subMonth())),
            'jml_transaksi_bulan' => $bulan($now)->count(),
            'biaya_bulan_ini' => (int) $biayaBulan($now)->sum('jumlah'),
            'jml_biaya'  => $biayaBulan($now)->count(),
            'tren'       => $tren,
            'per_layanan' => $bulan($now)->where('status', 'lunas')
                ->selectRaw('layanan, SUM(total) total')->groupBy('layanan')->orderByDesc('total')->get(),
            'biaya_kategori' => $biayaBulan($now)
                ->selectRaw('kategori, SUM(jumlah) total')->groupBy('kategori')->orderByDesc('total')->get(),
            'bulanan'    => $bulanan,
            'aktivitas'  => $aktivitas,
        ]);
    }

    public function transaksi(Request $r)
    {
        $this->pastikanOwner($r);
        $q = Transaksi::query()->latest()
            ->when($r->status, fn($x, $s) => $x->where('status', $s))
            ->when($r->q, fn($x, $s) => $x->where(fn($w) => $w->where('kode', 'like', "%$s%")->orWhere('pelanggan', 'like', "%$s%")));
        return response()->json(['total' => $q->count(), 'data' => $q->take(50)->get()]);
    }

    public function biaya(Request $r)
    {
        $this->pastikanOwner($r);
        return response()->json(['data' => Biaya::latest('tanggal')->take(50)->get()]);
    }
}