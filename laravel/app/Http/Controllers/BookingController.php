<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    private function tbl(string $base): string
    {
        return Schema::hasTable($base) ? $base : $base . 's';
    }

    private function role($user): string
    {
        return strtoupper((string) ($user->role ?? ''));
    }

    private function isStaff($user): bool
    {
        return in_array($this->role($user), ['ADMIN', 'KASIR', 'OWNER'], true);
    }

    private function canProcess($user): bool
    {
        return in_array($this->role($user), ['ADMIN', 'KASIR'], true);
    }

    private function fail(string $msg, int $code = 422)
    {
        return response()->json(['success' => false, 'message' => $msg], $code);
    }

    private function pelangganId($user)
    {
        $t = $this->tbl('pelanggan');
        if (isset($user->id_pelanggan) && $user->id_pelanggan) {
            return $user->id_pelanggan;
        }
        if (isset($user->id_user) && Schema::hasColumn($t, 'id_user')) {
            $r = DB::table($t)->where('id_user', $user->id_user)->first();
            if ($r) {
                return $r->id_pelanggan;
            }
        }
        $r = DB::table($t)->where('no_telepon', (string) ($user->username ?? ''))->first();
        return $r->id_pelanggan ?? null;
    }

    private function hydrate($rows)
    {
        $rows = collect($rows);
        if ($rows->isEmpty()) {
            return collect();
        }
        $lay = $this->tbl('layanan');
        $pel = $this->tbl('pelanggan');
        $det = DB::table('booking_detail as d')
            ->leftJoin("$lay as l", 'l.id_layanan', '=', 'd.id_layanan')
            ->whereIn('d.id_booking', $rows->pluck('id_booking')->all())
            ->select('d.id_booking', 'd.id_layanan', 'd.jumlah', 'l.nama_layanan', 'l.jenis_satuan', 'l.harga_per_satuan')
            ->get()->groupBy('id_booking');
        $pelMap = DB::table($pel)
            ->whereIn('id_pelanggan', $rows->pluck('id_pelanggan')->unique()->all())
            ->get()->keyBy('id_pelanggan');

        return $rows->map(function ($b) use ($det, $pelMap) {
            $p = $pelMap->get($b->id_pelanggan);
            $b->nama_pelanggan = $p->nama ?? '-';
            $b->no_telepon = $p->no_telepon ?? '-';
            $b->detail = $det->get($b->id_booking, collect())->values();
            return $b;
        })->values();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $q = DB::table('booking')->orderByDesc('id_booking');

        if (!$this->isStaff($user)) {
            $pid = $this->pelangganId($user);
            if (!$pid) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $q->where('id_pelanggan', $pid);
        }
        if ($request->filled('status')) {
            $q->where('status', strtoupper((string) $request->input('status')));
        }

        return response()->json(['success' => true, 'data' => $this->hydrate($q->limit(300)->get())]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($this->role($user) !== 'CUSTOMER') {
            return $this->fail('Booking hanya bisa dibuat dari akun pelanggan.', 403);
        }
        $pid = $this->pelangganId($user);
        if (!$pid) {
            return $this->fail('Data pelanggan untuk akun ini tidak ditemukan. Silakan hubungi admin.');
        }

        $data = $request->validate([
            'tanggal_booking' => 'required|date|after_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'detail' => 'nullable|array',
            'detail.*.id_layanan' => 'required|integer',
            'detail.*.jumlah' => 'required|numeric|min:0.1',
        ], [
            'tanggal_booking.required' => 'Tanggal datang wajib diisi.',
            'tanggal_booking.after_or_equal' => 'Tanggal datang tidak boleh sebelum hari ini.',
            'detail.required' => 'Pilih minimal satu layanan.',
            'detail.min' => 'Pilih minimal satu layanan.',
            'detail.*.jumlah.min' => 'Jumlah layanan tidak valid.',
        ]);

        $aktif = DB::table('booking')->where('id_pelanggan', $pid)
            ->whereIn('status', ['MENUNGGU', 'DIPANGGIL', 'DIKONFIRMASI'])->count();
        if ($aktif >= 3) {
            return $this->fail('Kamu masih punya 3 booking aktif. Selesaikan atau batalkan salah satunya dulu.');
        }

        $gabung = [];
        foreach (($data['detail'] ?? []) as $d) {
            $k = (int) $d['id_layanan'];
            $gabung[$k] = ($gabung[$k] ?? 0) + (float) $d['jumlah'];
        }

        $layanan = DB::table($this->tbl('layanan'))
            ->whereIn('id_layanan', array_keys($gabung))->where('is_active', 1)->get()->keyBy('id_layanan');
        $kg = 0;
        foreach ($gabung as $idL => $jml) {
            $l = $layanan->get($idL);
            if (!$l) {
                return $this->fail('Ada layanan yang sudah tidak tersedia. Muat ulang halaman.');
            }
            if (strtolower(trim((string) $l->jenis_satuan)) === 'kg') {
                $kg += $jml;
            }
        }
        if ($kg > 10) {
            return $this->fail('Total laundry kiloan maksimal 10 kg dalam satu booking.');
        }

        $id = DB::transaction(function () use ($data, $pid, $gabung) {
            $tgl = $data['tanggal_booking'];
            $urutan = ((int) DB::table('booking')->where('tanggal_booking', $tgl)->lockForUpdate()->max('urutan')) + 1;
            $idB = DB::table('booking')->insertGetId([
                'id_pelanggan' => $pid,
                'tanggal_booking' => $tgl,
                'urutan' => $urutan,
                'nomor_antrean' => 'A-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
                'status' => 'MENUNGGU',
                'catatan' => $data['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_booking');
            foreach ($gabung as $idL => $jml) {
                DB::table('booking_detail')->insert(['id_booking' => $idB, 'id_layanan' => $idL, 'jumlah' => $jml]);
            }
            return $idB;
        });

        $baru = $this->hydrate(DB::table('booking')->where('id_booking', $id)->get())->first();
        return response()->json(['success' => true, 'message' => 'Booking berhasil dibuat.', 'data' => $baru], 201);
    }

    private function transition(Request $request, $id, string $to, array $from)
    {
        if (!$this->canProcess($request->user())) {
            return $this->fail('Hanya kasir atau admin yang dapat memproses antrean.', 403);
        }
        return DB::transaction(function () use ($id, $to, $from) {
            $b = DB::table('booking')->where('id_booking', $id)->lockForUpdate()->first();
            if (!$b) {
                return $this->fail('Booking tidak ditemukan.', 404);
            }
            if (!in_array($b->status, $from, true)) {
                return $this->fail('Status booking saat ini (' . $b->status . ') tidak bisa diubah ke ' . $to . '.');
            }
            DB::table('booking')->where('id_booking', $id)->update(['status' => $to, 'updated_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Status booking diperbarui.']);
        });
    }

    public function panggil(Request $request, $id)
    {
        return $this->transition($request, $id, 'DIPANGGIL', ['MENUNGGU']);
    }

    public function konfirmasi(Request $request, $id)
    {
        return $this->transition($request, $id, 'DIKONFIRMASI', ['MENUNGGU', 'DIPANGGIL']);
    }

    public function batal(Request $request, $id)
    {
        $user = $request->user();
        $alasan = trim((string) $request->input('alasan', ''));
        $staff = $this->canProcess($user);
        if (!$staff) {
            return $this->fail('Pembatalan booking dilakukan oleh kasir. Silakan hubungi admin.', 403);
        }

        return DB::transaction(function () use ($user, $id, $alasan, $staff) {
            $b = DB::table('booking')->where('id_booking', $id)->lockForUpdate()->first();
            if (!$b) {
                return $this->fail('Booking tidak ditemukan.', 404);
            }
            if (in_array($b->status, ['SELESAI', 'DIBATALKAN'], true)) {
                return $this->fail('Booking ini sudah ' . strtolower($b->status) . '.');
            }
            if (!$staff) {
                $pid = $this->pelangganId($user);
                if (!$pid || (int) $b->id_pelanggan !== (int) $pid) {
                    return $this->fail('Booking ini bukan milikmu.', 403);
                }
                if ($b->status !== 'MENUNGGU') {
                    return $this->fail('Booking yang sudah dipanggil atau dikonfirmasi hanya bisa dibatalkan oleh kasir.');
                }
                $alasan = $alasan !== '' ? $alasan : 'Dibatalkan pelanggan';
            } elseif ($alasan === '') {
                return $this->fail('Alasan pembatalan wajib diisi.');
            }
            DB::table('booking')->where('id_booking', $id)->update([
                'status' => 'DIBATALKAN',
                'alasan_batal' => mb_substr($alasan, 0, 255),
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Booking dibatalkan.']);
        });
    }

    public function selesai(Request $request, $id)
    {
        if (!$this->canProcess($request->user())) {
            return $this->fail('Hanya kasir atau admin yang dapat memproses antrean.', 403);
        }
        $idT = (int) $request->input('id_transaksi');
        if (!$idT) {
            return $this->fail('ID transaksi wajib diisi.');
        }
        return DB::transaction(function () use ($id, $idT) {
            $b = DB::table('booking')->where('id_booking', $id)->lockForUpdate()->first();
            if (!$b) {
                return $this->fail('Booking tidak ditemukan.', 404);
            }
            if ($b->id_transaksi || $b->status === 'SELESAI') {
                return $this->fail('Booking ini sudah dibuat menjadi transaksi.');
            }
            if ($b->status !== 'DIKONFIRMASI') {
                return $this->fail('Konfirmasi kedatangan pelanggan terlebih dahulu.');
            }
            DB::table('booking')->where('id_booking', $id)->update([
                'status' => 'SELESAI',
                'id_transaksi' => $idT,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Booking ditandai selesai.']);
        });
    }
}