<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use RuntimeException;

class Sprint3ErrorHandlingTest extends TestCase
{
    public function test_api_validation_error_returns_422_json(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_unknown_api_endpoint_returns_404_json(): void
    {
        $this->getJson('/api/sprint3-endpoint-yang-tidak-ada')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_unexpected_api_exception_returns_safe_500_json(): void
    {
        Route::get('/api/__sprint3_test_server_error', function () {
            throw new RuntimeException('SPRINT3_TEST_INTERNAL_SECRET');
        });

        $this->getJson('/api/__sprint3_test_server_error')
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
            ])
            ->assertDontSee('SPRINT3_TEST_INTERNAL_SECRET');
    }
}
