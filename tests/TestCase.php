<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for testing
        $this->artisan('migrate');
    }

    /**
     * Create a user and return authentication token
     */
    protected function createUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Create an admin user and return authentication token
     */
    protected function createAdminWithToken(): array
    {
        return $this->createUserWithToken(['role' => 'admin']);
    }

    /**
     * Get headers with authentication token
     */
    protected function getAuthHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
