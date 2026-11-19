<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PemulihanEmailController extends Controller
{
    private function akun($email)
    {
        return DB::table('pelanggan')
            ->join('users', 'pelanggan.id_user', '=', 'users.id_user')
            ->where('pelanggan.email_pemulihan', $email)
            ->whereNotNull('pelanggan.email_pemulihan_verified_at')
            ->where('users.role', 'CUSTOMER')
            ->where('users.is_active', 1)
            ->select('pelanggan.id_pelanggan', 'pelanggan.no_telepon', 'users.id_user', 'users.username')
            ->first();
    }

    public function kirimOtp(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        $email = strtolower(trim($request->email));

        $last = DB::table('email_otp')
            ->where('email', $email)
            ->where('purpose', 'pemulihan')
            ->latest('id')
            ->first();

        if ($last && now()->parse($last->created_at)->addSeconds(60)->isFuture()) {
            return response()->json(['success' => false, 'message' => 'Tunggu 60 detik sebelum meminta OTP lagi.'], 429);
        }

        $kode = (string) random_int(100000, 999999);

        DB::table('email_otp')->where('email', $email)->where('purpose', 'pemulihan')->delete();

        DB::table('email_otp')->insert([
            'email' => $email,
            'purpose' => 'pemulihan',
            'code_hash' => Hash::make($kode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::raw(
                "Halo!\n\nKode verifikasi pemulihan akun Amih Laundry kamu adalah: {$kode}\n\nKode berlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Verifikasi Pemulihan Akun - Amih Laundry');
                }
            );
        } catch (\Throwable $e) {
            DB::table('email_otp')->where('email', $email)->where('purpose', 'pemulihan')->delete();

            return response()->json([
                'success' => false,
                'message' => 'Email gagal dikirim. Periksa konfigurasi SMTP aplikasi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi telah dikirim ke email yang kamu masukkan.',
        ]);
    }

    public function verifikasiOtp(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'kode' => 'required|digits:6',
            'nama' => 'required|string|max:100',
            'kode_transaksi' => 'required|string|max:100',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        $email = strtolower(trim($request->email));

        $otp = DB::table('email_otp')
            ->where('email', $email)
            ->where('purpose', 'pemulihan')
            ->latest('id')
            ->first();

        if (!$otp || now()->greaterThan($otp->expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP tidak ditemukan atau sudah kedaluwarsa.'], 422);
        }

        if ($otp->attempts >= 5) {
            return response()->json(['success' => false, 'message' => 'Batas percobaan habis. Minta OTP baru.'], 429);
        }

        if (!Hash::check($request->kode, $otp->code_hash)) {
            DB::table('email_otp')->where('id', $otp->id)->increment('attempts');

            return response()->json(['success' => false, 'message' => 'Kode OTP salah.'], 422);
        }

        if ($otp->verified_at) {
            return response()->json(['success' => false, 'message' => 'OTP sudah digunakan. Minta kode baru.'], 422);
        }

        $akun = DB::table('pelanggan')
            ->join('transaksi', 'pelanggan.id_pelanggan', '=', 'transaksi.id_pelanggan')
            ->join('users', 'pelanggan.id_user', '=', 'users.id_user')
            ->where('pelanggan.nama', trim($request->nama))
            ->where('transaksi.kode_transaksi', trim($request->kode_transaksi))
            ->where('users.role', 'CUSTOMER')
            ->where('users.is_active', 1)
            ->select(
                'pelanggan.id_pelanggan',
                'pelanggan.no_telepon',
                'users.id_user',
                'users.username'
            )
            ->first();

        if (!$akun) {
            DB::table('email_otp')->where('id', $otp->id)->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'Nama atau kode transaksi tidak cocok. Periksa kembali data pada struk transaksi.',
            ], 422);
        }

        $emailDipakai = DB::table('pelanggan')
            ->where('email_pemulihan', $email)
            ->where('id_pelanggan', '!=', $akun->id_pelanggan)
            ->exists();

        if ($emailDipakai) {
            return response()->json([
                'success' => false,
                'message' => 'Email tersebut sudah terhubung dengan akun lain.',
            ], 422);
        }

        DB::table('pelanggan')
            ->where('id_pelanggan', $akun->id_pelanggan)
            ->update([
                'email_pemulihan' => $email,
                'email_pemulihan_verified_at' => now(),
            ]);

        $token = Str::random(64);

        DB::table('email_otp')->where('id', $otp->id)->update([
            'verified_at' => now(),
            'token_hash' => hash('sha256', $token),
            'updated_at' => now(),
        ]);

        $phone = (string) $akun->no_telepon;
        $masked = strlen($phone) > 4
            ? str_repeat('*', strlen($phone) - 4) . substr($phone, -4)
            : '****';

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditemukan dan email berhasil diverifikasi.',
            'data' => [
                'username' => $akun->username,
                'masked_phone' => $masked,
                'token' => $token,
            ],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        $email = strtolower(trim($request->email));

        $otp = DB::table('email_otp')
            ->where('email', $email)
            ->where('purpose', 'pemulihan')
            ->whereNotNull('verified_at')
            ->where('token_hash', hash('sha256', $request->token))
            ->latest('id')
            ->first();

        if (!$otp || now()->greaterThan($otp->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pemulihan berakhir. Ulangi proses dari awal.',
            ], 422);
        }

        $akun = $this->akun($email);

        if (!$akun) {
            return response()->json(['success' => false, 'message' => 'Akun tidak ditemukan.'], 404);
        }

        DB::table('users')->where('id_user', $akun->id_user)->update([
            'password_hash' => Hash::make($request->password),
        ]);

        DB::table('email_otp')->where('id', $otp->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }
}
