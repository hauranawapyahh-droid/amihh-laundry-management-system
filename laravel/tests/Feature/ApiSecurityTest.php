<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    public function test_login_rejects_missing_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
    }

    public function test_customer_dashboard_rejects_missing_token(): void
    {
        $response = $this->getJson('/api/customer/dashboard');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_user_management_rejects_missing_token(): void
    {
        $response = $this->getJson('/api/pengguna');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'akun_tidak_valid_987654',
            'password' => 'password_salah_987654',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }
}
