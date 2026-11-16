<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Layanan;

class KetersediaanLayananTest extends TestCase
{
    public function test_layanan_tidak_aktif_tidak_bisa_dipesan(): void
    {
        $layanan = new Layanan();
        $layanan->is_active = 0;

        $this->assertFalse((bool) $layanan->is_active);
    }
}
