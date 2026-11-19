<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Models\Pembayaran;

/**
 * Data untuk dashboard pelanggan (hanya membaca).
 *
 * Pelanggan hanya boleh melihat transaksi miliknya sendiri, jadi dashboard
 * ini TIDAK memakai /api/transaksi (isinya semua pelanggan).
 *
 * ROUTE (sudah ada di routes/api.php, di dalam grup 'jwt'):
 *     Route::middleware('role:CUSTOMER')->prefix('customer')->group(function () {
 *         Route::get('/dashboard', [CustomerController::class, 'dashboard']);
 *         Route::put('/password',  [CustomerController::class, 'ubahPassword']);
 *     });
 *
 * HUBUNGAN AKUN <-> PELANGGAN:
 * Tabel pelanggan belum punya kolom penghubung ke users, jadi akun customer
 * dihubungkan lewat USERNAME = NO. TELEPON pelanggan.
 * Contoh: pelanggan dengan no_telepon 081234567890 -> buat pengguna role
 * CUSTOMER dengan username 081234567890.
 * (Kalau nanti tabel pelanggan diberi kolom id_user, controller ini otomatis
 * memakainya lebih dulu.)
 */
class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Belum login.',
            ], 401);
        }

        $pelanggan = $this->cariPelanggan($user);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini belum terhubung ke data pelanggan. '
                    . 'Pastikan username sama dengan nomor telepon pelanggan, atau hubungi admin.',
            ], 404);
        }

        // Transaksi milik pelanggan ini saja
        $transaksi = Transaksi::with(['detail.layanan', 'prosesCucian', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderByDesc('id_transaksi')
            ->get()
            ->map(function ($t) {
                $pembayaranTerakhir = Pembayaran::where('id_transaksi', $t->id_transaksi)->orderByDesc('id_pembayaran')->first();
                return [
                    'id_transaksi'       => $t->id_transaksi,
                    'kode_transaksi'     => $t->kode_transaksi,
                    'total_harga'        => $t->total_harga,
                    'status_pembayaran'  => $t->status_pembayaran,
                    'status_konfirmasi' => $pembayaranTerakhir->status_konfirmasi ?? null,
                    'bukti_pembayaran' => $pembayaranTerakhir->bukti_pembayaran ?? null,
                    'pembayaran' => $t->pembayaran->map(function ($p) {
                        return [
                            'status_konfirmasi' => $p->status_konfirmasi,
                            'bukti_pembayaran' => $p->bukti_pembayaran,
                            'metode_pembayaran' => $p->metode_pembayaran,
                        ];
                    })->values(),
                    'status_pengambilan' => $t->status_pengambilan,
                    'waktu_pengambilan'  => $t->waktu_pengambilan,
                    'catatan'            => $t->catatan,
                    'created_at'         => $t->created_at,

                    // rincian layanan (nama kunci disesuaikan dengan dashboard)
                    'items' => $t->detail->map(function ($d) {
                        return [
                            'nama_layanan'     => optional($d->layanan)->nama_layanan,
                            'jenis_satuan'     => optional($d->layanan)->jenis_satuan,
                            'jumlah'           => $d->berat_jumlah,
                            'harga_per_satuan' => $d->harga_satuan,
                            'subtotal'         => $d->subtotal,
                        ];
                    })->values(),

                    // riwayat proses dari staff laundry
                    'proses' => $t->prosesCucian->map(function ($p) {
                        return [
                            'id_proses'      => $p->id_proses,
                            'status_proses'  => $p->status_proses,
                            'catatan_proses' => $p->catatan_proses,
                            'created_at'     => $p->created_at,
                        ];
                    })->values(),
                ];
            })
            ->values();

        // Layanan aktif untuk menu Layanan & Harga
        $layanan = Layanan::where('is_active', 1)
            ->orderBy('nama_layanan')
            ->get(['id_layanan', 'nama_layanan', 'jenis_satuan', 'harga_per_satuan']);

        return response()->json([
            'success' => true,
            'data' => [
                'pelanggan' => [
                    'nama'       => $pelanggan->nama,
                    'no_telepon' => $pelanggan->no_telepon,
                    'alamat'     => $pelanggan->alamat,
                    'created_at' => $pelanggan->created_at ?? null,
                    'email_pemulihan' => $pelanggan->email_pemulihan,
                    'email_pemulihan_verified' => !empty($pelanggan->email_pemulihan_verified_at),
                ],
                'layanan'   => $layanan,
                'transaksi' => $transaksi,
            ],
        ]);
    }

    /**
     * Pelanggan mengganti password akunnya sendiri.
     * Body: password_lama, password_baru (minimal 6 karakter)
     */
    public function ubahPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6',
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min'      => 'Password baru minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = auth()->user();

        if (!password_verify($request->password_lama, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama salah.',
            ], 422);
        }

        $user->password_hash = password_hash($request->password_baru, PASSWORD_DEFAULT);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    /**
     * Cari data pelanggan milik user yang login.
     * 1) lewat kolom pelanggan.id_user (kalau kolomnya ada)
     * 2) username user = no_telepon pelanggan
     */
    public function kirimOtpEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $pelanggan = $this->cariPelanggan(auth()->user());

        if (!$pelanggan) {
            return response()->json(['success' => false, 'message' => 'Data pelanggan tidak ditemukan.'], 404);
        }

        $email = strtolower(trim($request->email));

        $dipakai = Pelanggan::where('email_pemulihan', $email)
            ->where('id_pelanggan', '!=', $pelanggan->id_pelanggan)
            ->exists();

        if ($dipakai) {
            return response()->json(['success' => false, 'message' => 'Email sudah digunakan akun lain.'], 422);
        }

        $terakhir = DB::table('email_otp')
            ->where('email', $email)
            ->where('purpose', 'registrasi')
            ->latest('id')
            ->first();

        if ($terakhir && Carbon::parse($terakhir->created_at)->addSeconds(60)->isFuture()) {
            return response()->json(['success' => false, 'message' => 'Tunggu 60 detik sebelum meminta OTP lagi.'], 429);
        }

        $kode = (string) random_int(100000, 999999);

        DB::table('email_otp')->where('email', $email)->where('purpose', 'registrasi')->delete();

        DB::table('email_otp')->insert([
            'email' => $email,
            'purpose' => 'registrasi',
            'code_hash' => Hash::make($kode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::raw("Kode verifikasi email Amih Laundry: {$kode}\nKode berlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun.", function ($message) use ($email) {
                $message->to($email)->subject('Verifikasi Email Pemulihan Amih Laundry');
            });
        } catch (\Throwable $e) {
            DB::table('email_otp')->where('email', $email)->where('purpose', 'registrasi')->delete();
            return response()->json(['success' => false, 'message' => 'Email gagal dikirim. Periksa konfigurasi email aplikasi.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Kode OTP berhasil dikirim ke email.']);
    }

    public function verifikasiOtpEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'kode' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $pelanggan = $this->cariPelanggan(auth()->user());

        if (!$pelanggan) {
            return response()->json(['success' => false, 'message' => 'Data pelanggan tidak ditemukan.'], 404);
        }

        $email = strtolower(trim($request->email));

        $otp = DB::table('email_otp')
            ->where('email', $email)
            ->where('purpose', 'registrasi')
            ->latest('id')
            ->first();

        if (!$otp || Carbon::parse($otp->expires_at)->isPast()) {
            return response()->json(['success' => false, 'message' => 'OTP tidak ditemukan atau sudah kedaluwarsa.'], 422);
        }

        if ($otp->attempts >= 5) {
            return response()->json(['success' => false, 'message' => 'Batas percobaan OTP habis. Minta kode baru.'], 429);
        }

        if (!Hash::check($request->kode, $otp->code_hash)) {
            DB::table('email_otp')->where('id', $otp->id)->increment('attempts');
            return response()->json(['success' => false, 'message' => 'Kode OTP salah.'], 422);
        }

        $dipakai = Pelanggan::where('email_pemulihan', $email)
            ->where('id_pelanggan', '!=', $pelanggan->id_pelanggan)
            ->exists();

        if ($dipakai) {
            return response()->json(['success' => false, 'message' => 'Email sudah digunakan akun lain.'], 422);
        }

        $pelanggan->email_pemulihan = $email;
        $pelanggan->email_pemulihan_verified_at = now();
        $pelanggan->save();

        DB::table('email_otp')->where('id', $otp->id)->delete();

        return response()->json(['success' => true, 'message' => 'Email pemulihan berhasil diverifikasi.']);
    }
    private function cariPelanggan($user)
    {
        $table = (new Pelanggan())->getTable();

        if (Schema::hasColumn($table, 'id_user')) {
            $p = Pelanggan::where('id_user', $user->id_user)->first();
            if ($p) {
                return $p;
            }
        }

        return Pelanggan::where('no_telepon', $user->username)->first();
    }
    public function unggahBukti(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'metode_pembayaran' => 'required|in:TRANSFER_BANK,QRIS',
        'bukti_pembayaran' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $user = auth()->user();
    $pelanggan = $this->cariPelanggan($user);

    if (!$pelanggan) {
        return response()->json([
            'success' => false,
            'message' => 'Akun belum terhubung ke data pelanggan.',
        ], 404);
    }

    $transaksi = Transaksi::where('id_transaksi', $id)
        ->where('id_pelanggan', $pelanggan->id_pelanggan)
        ->first();

    if (!$transaksi) {
        return response()->json([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan.',
        ], 404);
    }

    if ($transaksi->status_pembayaran === 'LUNAS') {
        return response()->json([
            'success' => false,
            'message' => 'Transaksi sudah lunas.',
        ], 422);
    }

    $pembayaran = Pembayaran::where('id_transaksi', $id)
        ->latest('id_pembayaran')
        ->first();

    if ($pembayaran && $pembayaran->status_konfirmasi === 'CONFIRMED') {
        return response()->json([
            'success' => false,
            'message' => 'Pembayaran sudah dikonfirmasi.',
        ], 422);
    }

    $path = $request->file('bukti_pembayaran')
        ->store('bukti-pembayaran', 'public');

    if (!$pembayaran) {
        $pembayaran = new Pembayaran();
        $pembayaran->id_transaksi = $transaksi->id_transaksi;
    }

    $pembayaran->metode_pembayaran = $request->metode_pembayaran;
    $pembayaran->jumlah_bayar = $transaksi->total_harga;
    $pembayaran->kembalian = 0;
    $pembayaran->status_konfirmasi = 'PENDING';
    $pembayaran->bukti_pembayaran = $path;
    $pembayaran->catatan_pelunasan = 'Menunggu verifikasi kasir.';
    $pembayaran->id_konfirmator = null;
    $pembayaran->waktu_konfirmasi = null;
    $pembayaran->save();

    return response()->json([
        'success' => true,
        'message' => 'Bukti berhasil diunggah. Menunggu verifikasi kasir.',
        'data' => [
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'status_konfirmasi' => $pembayaran->status_konfirmasi,
        ],
    ], 201);
}
}

