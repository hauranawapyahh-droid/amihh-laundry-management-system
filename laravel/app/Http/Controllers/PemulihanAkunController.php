<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PemulihanAkunController extends Controller
{
    private const TTL_MENIT = 5;
    private const MAKS_SALAH = 5;
    private const JEDA_DETIK = 60;

    private function fail(string $msg, int $code = 422, array $extra = [])
    {
        return response()->json(array_merge(['success' => false, 'message' => $msg], $extra), $code);
    }

    private function normalisasi(string $no): string
    {
        $d = preg_replace('/\D+/', '', $no);
        if ($d === '') {
            return '';
        }
        if (substr($d, 0, 2) === '62') {
            $d = '0' . substr($d, 2);
        } elseif ($d[0] !== '0') {
            $d = '0' . $d;
        }
        return $d;
    }

    private function varian(string $n): array
    {
        $tanpaNol = substr($n, 1);
        return array_values(array_unique([$n, '62' . $tanpaNol, '+62' . $tanpaNol, $tanpaNol]));
    }

    private function hashKode(string $kode): string
    {
        return hash_hmac('sha256', $kode, (string) config('app.key'));
    }

    private function cariAkun(string $n): ?array
    {
        $tp = Schema::hasTable('pelanggan') ? 'pelanggan' : 'pelanggans';
        $tu = null;
        foreach (['users', 'pengguna', 'user'] as $cand) {
            if (Schema::hasTable($cand) && Schema::hasColumn($cand, 'username')) {
                $tu = $cand;
                break;
            }
        }
        if (!Schema::hasTable($tp) || !$tu) {
            return null;
        }
        $pk = Schema::hasColumn($tu, 'id_user') ? 'id_user' : 'id';
        $v = $this->varian($n);

        $pel = DB::table($tp)->whereIn('no_telepon', $v)->first();
        $user = null;
        if ($pel && Schema::hasColumn($tp, 'id_user') && !empty($pel->id_user)) {
            $user = DB::table($tu)->where($pk, $pel->id_user)->first();
        }
        if (!$user) {
            $user = DB::table($tu)->whereIn('username', $v)->first();
        }
        if (!$user || strtoupper((string) ($user->role ?? '')) !== 'CUSTOMER') {
            return null;
        }
        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            return null;
        }
        return ['user' => $user, 'tabel' => $tu, 'pk' => $pk];
    }

    public function kirimOtp(Request $request)
    {
        $data = $request->validate(['no_hp' => 'required|string|max:25'], [
            'no_hp.required' => 'Nomor HP wajib diisi.',
        ]);
        $n = $this->normalisasi($data['no_hp']);
        if (strlen($n) < 10 || strlen($n) > 14) {
            return $this->fail('Nomor HP tidak valid.');
        }

        $token = config('otp.fonnte_token');
        $lokal = app()->environment('local');
        if (!$token && !$lokal) {
            return $this->fail('Layanan kode verifikasi belum aktif. Silakan hubungi admin lewat WhatsApp.', 503, ['butuh_admin' => true]);
        }

        $terakhir = DB::table('pemulihan_otp')->where('no_telepon', $n)->orderByDesc('id')->first();
        if ($terakhir && $terakhir->created_at) {
            $sisa = self::JEDA_DETIK - now()->diffInSeconds($terakhir->created_at);
            if ($sisa > 0 && now()->greaterThanOrEqualTo($terakhir->created_at)) {
                return $this->fail('Tunggu ' . (int) $sisa . ' detik sebelum meminta kode lagi.', 429);
            }
        }

        $umum = ['success' => true, 'message' => 'Jika nomor terdaftar, kode verifikasi sudah dikirim lewat WhatsApp.'];
        $akun = $this->cariAkun($n);
        if (!$akun) {
            return response()->json($umum);
        }

        $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        DB::table('pemulihan_otp')->where('no_telepon', $n)->delete();
        $id = DB::table('pemulihan_otp')->insertGetId([
            'no_telepon' => $n,
            'kode_hash' => $this->hashKode($kode),
            'percobaan' => 0,
            'kedaluwarsa' => now()->addMinutes(self::TTL_MENIT),
            'created_at' => now(),
        ]);

        if ($token) {
            try {
                $resp = Http::withHeaders(['Authorization' => $token])->asForm()->timeout(15)
                    ->post('https://api.fonnte.com/send', [
                        'target' => $n,
                        'message' => "Kode verifikasi Amih Laundry: {$kode}\nBerlaku " . self::TTL_MENIT . " menit. Jangan berikan kode ini kepada siapa pun.",
                    ]);
                $ok = $resp->successful() && $resp->json('status') !== false;
            } catch (\Throwable $e) {
                Log::error('OTP gagal dikirim: ' . $e->getMessage());
                $ok = false;
            }
            if (!$ok) {
                DB::table('pemulihan_otp')->where('id', $id)->delete();
                return $this->fail('Kode gagal dikirim. Coba lagi sebentar lagi atau hubungi admin lewat WhatsApp.', 502, ['butuh_admin' => true]);
            }
            return response()->json($umum);
        }

        Log::info("OTP lokal untuk {$n}: {$kode}");
        return response()->json($umum + ['dev_kode' => $kode]);
    }

    public function verifikasiOtp(Request $request)
    {
        $data = $request->validate([
            'no_hp' => 'required|string|max:25',
            'kode' => 'required|string|size:6',
        ], ['kode.size' => 'Kode verifikasi terdiri dari 6 angka.']);
        $n = $this->normalisasi($data['no_hp']);

        $row = DB::table('pemulihan_otp')->where('no_telepon', $n)->whereNull('diverifikasi_pada')->orderByDesc('id')->first();
        if (!$row || now()->greaterThan($row->kedaluwarsa)) {
            return $this->fail('Kode tidak valid atau sudah kedaluwarsa. Minta kode baru.');
        }
        if ((int) $row->percobaan >= self::MAKS_SALAH) {
            DB::table('pemulihan_otp')->where('id', $row->id)->delete();
            return $this->fail('Terlalu banyak percobaan salah. Minta kode baru.', 429);
        }
        if (!hash_equals($row->kode_hash, $this->hashKode($data['kode']))) {
            DB::table('pemulihan_otp')->where('id', $row->id)->increment('percobaan');
            $sisa = max(0, self::MAKS_SALAH - ((int) $row->percobaan + 1));
            return $this->fail('Kode salah. Sisa percobaan: ' . $sisa . '.');
        }

        $akun = $this->cariAkun($n);
        if (!$akun) {
            return $this->fail('Akun tidak ditemukan. Silakan hubungi admin.');
        }

        $token = Str::random(60);
        DB::table('pemulihan_otp')->where('id', $row->id)->update([
            'diverifikasi_pada' => now(),
            'kedaluwarsa' => now()->addMinutes(10),
            'token_hash' => hash('sha256', $token),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi berhasil.',
            'data' => [
                'username' => $akun['user']->username,
                'nama' => $akun['user']->nama ?? null,
                'token' => $token,
            ],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'no_hp' => 'required|string|max:25',
            'token' => 'required|string|max:100',
            'password' => 'required|string|min:6|max:100|confirmed',
        ], [
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);
        $n = $this->normalisasi($data['no_hp']);

        $row = DB::table('pemulihan_otp')
            ->where('no_telepon', $n)
            ->where('token_hash', hash('sha256', $data['token']))
            ->whereNotNull('diverifikasi_pada')
            ->first();
        if (!$row || now()->greaterThan($row->kedaluwarsa)) {
            return $this->fail('Sesi pemulihan sudah berakhir. Ulangi dari awal.');
        }

        $akun = $this->cariAkun($n);
        if (!$akun) {
            return $this->fail('Akun tidak ditemukan. Silakan hubungi admin.');
        }

        $upd = ['password' => Hash::make($data['password'])];
        if (Schema::hasColumn($akun['tabel'], 'updated_at')) {
            $upd['updated_at'] = now();
        }
        DB::table($akun['tabel'])->where($akun['pk'], $akun['user']->{$akun['pk']})->update($upd);
        DB::table('pemulihan_otp')->where('no_telepon', $n)->delete();

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah. Silakan login.']);
    }
}