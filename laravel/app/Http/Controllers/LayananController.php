<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanan = Layanan::orderBy('id_layanan')->get();

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }
}