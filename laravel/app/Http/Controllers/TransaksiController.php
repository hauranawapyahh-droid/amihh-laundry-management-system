<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\ProsesCucian;
use App\Models\Pelanggan;
use App\Models\Layanan;

class TransaksiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR TRANSAKSI
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $data = Transaksi::with([
            'pelanggan',
            'kasir',
            'verifikator',
            'detail.layanan',
            'pembayaran',
            'prosesCucian.staff'
        ])
        ->orderByDesc('id_transaksi')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL TRANSAKSI
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $transaksi = Transaksi::with([
            'pelanggan',
            'kasir',
            'verifikator',
            'detail.layanan',
            'pembayaran',
            'prosesCucian.staff'
        ])->find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BUAT TRANSAKSI
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',

            'detail' => 'required|array|min:1',

            'detail.*.id_layanan' =>
                'required|integer|exists:layanan,id_layanan',

            'detail.*.berat_jumlah' =>
                'required|numeric|min:0.01',

            'pembayaran' => 'required|array',

            'pembayaran.status' =>
                'required|in:SEKARANG,NANTI',

            'pembayaran.metode_pembayaran' =>
                'nullable|in:TUNAI,TRANSFER_BANK,QRIS',

            'pembayaran.jumlah_bayar' =>
                'nullable|numeric|min:0',

            'pembayaran.catatan_pelunasan' =>
                'nullable|string',

            'catatan' =>
                'nullable|string',
        ]);


        try {

            $result = DB::transaction(function () use ($validated) {

                $user = auth()->user();


                /*
                |--------------------------------------------------------------------------
                | PELANGGAN
                |--------------------------------------------------------------------------
                */

                $pelanggan = Pelanggan::find(
                    $validated['id_pelanggan']
                );

                if (!$pelanggan) {
                    throw new \Exception(
                        'Pelanggan tidak ditemukan.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | HITUNG TOTAL
                |--------------------------------------------------------------------------
                */

                $total = 0;

                $detailData = [];


                foreach ($validated['detail'] as $item) {

                    $layanan = Layanan::find(
                        $item['id_layanan']
                    );

                    if (!$layanan) {
                        throw new \Exception(
                            'Layanan tidak ditemukan.'
                        );
                    }


                    if (!$layanan->is_active) {
                        throw new \Exception(
                            'Layanan sedang tidak aktif.'
                        );
                    }


                    $beratJumlah =
                        (float) $item['berat_jumlah'];

                    $harga =
                        (float) $layanan->harga_per_satuan;

                    $subtotal =
                        $beratJumlah * $harga;

                    $total += $subtotal;


                    $detailData[] = [
                        'id_layanan' =>
                            $layanan->id_layanan,

                        'berat_jumlah' =>
                            $beratJumlah,

                        'harga_satuan' =>
                            $harga,

                        'subtotal' =>
                            $subtotal,
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | BUAT KODE TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $kodeTransaksi =
                    'TRX-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            str_shuffle(
                                'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
                            ),
                            0,
                            4
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | BUAT TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transaksi = Transaksi::create([

                    'kode_transaksi' =>
                        $kodeTransaksi,

                    'id_pelanggan' =>
                        $pelanggan->id_pelanggan,

                    'id_kasir' =>
                        $user->id_user,

                    'id_verifikator' =>
                        null,

                    'total_harga' =>
                        $total,

                    'status_pembayaran' =>
                        'BELUM_DIBAYAR',

                    'status_pengambilan' =>
                        'BELUM_DIAMBIL',

                    'waktu_pengambilan' =>
                        null,

                    'catatan' =>
                        $validated['catatan'] ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | DETAIL TRANSAKSI
                |--------------------------------------------------------------------------
                */

                foreach ($detailData as $detail) {

                    DetailTransaksi::create([

                        'id_transaksi' =>
                            $transaksi->id_transaksi,

                        'id_layanan' =>
                            $detail['id_layanan'],

                        'berat_jumlah' =>
                            $detail['berat_jumlah'],

                        'harga_satuan' =>
                            $detail['harga_satuan'],

                        'subtotal' =>
                            $detail['subtotal'],
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                $pembayaranInput =
                    $validated['pembayaran'];


                if (
                    $pembayaranInput['status']
                    === 'SEKARANG'
                ) {

                    if (
                        empty(
                            $pembayaranInput[
                                'metode_pembayaran'
                            ]
                        )
                    ) {
                        throw new \Exception(
                            'Metode pembayaran wajib diisi.'
                        );
                    }


                    $jumlahBayar =
                        (float) (
                            $pembayaranInput[
                                'jumlah_bayar'
                            ] ?? 0
                        );


                    if ($jumlahBayar < $total) {
                        throw new \Exception(
                            'Jumlah pembayaran kurang dari total transaksi.'
                        );
                    }


                    $kembalian =
                        $jumlahBayar - $total;


                    Pembayaran::create([

                        'id_transaksi' =>
                            $transaksi->id_transaksi,

                        'metode_pembayaran' =>
                            $pembayaranInput[
                                'metode_pembayaran'
                            ],

                        'jumlah_bayar' =>
                            $jumlahBayar,

                        'kembalian' =>
                            $kembalian,

                        'status_konfirmasi' =>
                            'CONFIRMED',

                        'catatan_pelunasan' =>
                            $pembayaranInput[
                                'catatan_pelunasan'
                            ] ?? null,

                        'id_konfirmator' =>
                            $user->id_user,

                        'waktu_konfirmasi' =>
                            now(),
                    ]);


                    $transaksi->update([

                        'status_pembayaran' =>
                            'LUNAS',

                        'id_verifikator' =>
                            $user->id_user,
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | LOAD RELATION
                |--------------------------------------------------------------------------
                */

                $transaksi->load([
                    'pelanggan',
                    'kasir',
                    'detail.layanan',
                    'pembayaran',
                    'prosesCucian.staff'
                ]);


                return $transaksi;
            });


            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $result
            ], 201);


        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function pembayaran(
        Request $request,
        $id
    ) {

        $validated = $request->validate([

            'metode_pembayaran' =>
                'required|in:TUNAI,TRANSFER_BANK,QRIS',

            'jumlah_bayar' =>
                'required|numeric|min:0',

            'catatan_pelunasan' =>
                'nullable|string',
        ]);


        try {

            $result = DB::transaction(
                function () use (
                    $validated,
                    $id
                ) {

                    $user = auth()->user();


                    $transaksi =
                        Transaksi::find($id);


                    if (!$transaksi) {

                        throw new \Exception(
                            'Transaksi tidak ditemukan.'
                        );
                    }


                    if (
                        $transaksi->status_pembayaran
                        === 'LUNAS'
                    ) {

                        throw new \Exception(
                            'Transaksi sudah lunas.'
                        );
                    }


                    $jumlahBayar =
                        (float)
                        $validated['jumlah_bayar'];


                    $total =
                        (float)
                        $transaksi->total_harga;


                    if ($jumlahBayar < $total) {

                        throw new \Exception(
                            'Jumlah pembayaran kurang dari total transaksi.'
                        );
                    }


                    $kembalian =
                        $jumlahBayar - $total;


                    $pembayaran =
                        Pembayaran::create([

                            'id_transaksi' =>
                                $transaksi->id_transaksi,

                            'metode_pembayaran' =>
                                $validated[
                                    'metode_pembayaran'
                                ],

                            'jumlah_bayar' =>
                                $jumlahBayar,

                            'kembalian' =>
                                $kembalian,

                            'status_konfirmasi' =>
                                'CONFIRMED',

                            'catatan_pelunasan' =>
                                $validated[
                                    'catatan_pelunasan'
                                ] ?? null,

                            'id_konfirmator' =>
                                $user->id_user,

                            'waktu_konfirmasi' =>
                                now(),
                        ]);


                    $transaksi->update([

                        'status_pembayaran' =>
                            'LUNAS',

                        'id_verifikator' =>
                            $user->id_user,
                    ]);


                    return [

                        'transaksi' =>
                            $transaksi->fresh(),

                        'pembayaran' =>
                            $pembayaran,
                    ];
                }
            );


            return response()->json([

                'success' => true,

                'message' =>
                    'Pembayaran berhasil',

                'data' =>
                    $result
            ]);


        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage()

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROSES CUCIAN
    |--------------------------------------------------------------------------
    */

    public function prosesCucian()
    {
        $transaksi = Transaksi::with([
            'pelanggan',
            'detail.layanan'
        ])
        ->orderByDesc('id_transaksi')
        ->get();


        $proses = ProsesCucian::with([
            'transaksi.pelanggan',
            'transaksi.detail.layanan',
            'staff'
        ])
        ->orderByDesc('id_proses')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | AMBIL PROSES TERAKHIR PER TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $prosesTerakhir = $proses
            ->groupBy('id_transaksi')
            ->map(function ($items) {

                return $items
                    ->sortByDesc('id_proses')
                    ->first();
            });


        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN TRANSAKSI DENGAN PROSES
        |--------------------------------------------------------------------------
        */

        $data = $transaksi->map(
            function ($trx) use ($prosesTerakhir) {

                /*
                | Kalau sudah punya proses,
                | ambil proses terakhir.
                */

                if (
                    $prosesTerakhir
                        ->has($trx->id_transaksi)
                ) {

                    return $prosesTerakhir
                        ->get($trx->id_transaksi);
                }


                /*
                | Kalau transaksi baru dan belum punya
                | proses, tampilkan sebagai DITERIMA.
                |
                | Tidak INSERT ke database karena
                | id_staff tidak boleh NULL.
                */

                return [

                    'id_proses' =>
                        null,

                    'id_transaksi' =>
                        $trx->id_transaksi,

                    'id_staff' =>
                        null,

                    'status_proses' =>
                        'DITERIMA',

                    'catatan_proses' =>
                        'Cucian diterima dari pelanggan',

                    'created_at' =>
                        $trx->created_at,

                    'transaksi' =>
                        $trx,

                    'staff' =>
                        null,
                ];
            }
        );


        return response()->json([

            'success' => true,

            'data' => $data
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROSES CUCIAN
    |--------------------------------------------------------------------------
    */

    public function updateProses(
        Request $request,
        $id
    ) {

        $validated = $request->validate([

            'status_proses' =>
                'required|in:DITERIMA,DICUCI,DIKERINGKAN,DISETRIKA,SELESAI',

            'catatan_proses' =>
                'nullable|string',
        ]);


        $transaksi =
            Transaksi::find($id);


        if (!$transaksi) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Transaksi tidak ditemukan'

            ], 404);
        }


        $user =
            auth()->user();


        $proses =
            ProsesCucian::create([

                'id_transaksi' =>
                    $transaksi->id_transaksi,

                'id_staff' =>
                    $user->id_user,

                'status_proses' =>
                    $validated['status_proses'],

                'catatan_proses' =>
                    $validated[
                        'catatan_proses'
                    ] ?? null,
            ]);


        return response()->json([

            'success' => true,

            'message' =>
                'Status proses cucian berhasil diperbarui',

            'data' =>
                $proses->load('staff')
        ]);
    }
}