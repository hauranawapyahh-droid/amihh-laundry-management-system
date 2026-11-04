<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    public function test_login_rejects_missing_credentials(): void
    {
        $this->postJson('/api/auth/login', [])->assertStatus(422);
    }

    public function test_customer_dashboard_rejects_missing_token(): void
    {
        $this->getJson('/api/customer/dashboard')
            ->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_user_management_rejects_missing_token(): void
    {
        $this->getJson('/api/pengguna')
            ->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $this->postJson('/api/auth/login', [
            'username' => 'akun_tidak_valid_987654',
            'password' => 'password_salah_987654',
        ])->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_transaction_list_rejects_missing_token(): void
    {
        $this->getJson('/api/transaksi')->assertStatus(401);
    }

    public function test_reports_reject_missing_token(): void
    {
        $this->getJson('/api/laporan/pendapatan')->assertStatus(401);
    }

    public function test_laundry_process_rejects_missing_token(): void
    {
        $this->getJson('/api/proses-cucian')->assertStatus(401);
    }

    public function test_customer_password_change_rejects_missing_token(): void
    {
        $this->putJson('/api/customer/password', [])->assertStatus(401);
    }

    public function test_role_middleware_rejects_wrong_role(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $user = new \App\Models\User();
        $user->id_user = 987654;
        $user->nama = 'Test Customer';
        $user->username = 'test_customer_role';
        $user->role = 'CUSTOMER';
        $user->is_active = true;

        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($user);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/pengguna')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak memiliki akses ke fitur ini',
            ]);
    }
}

