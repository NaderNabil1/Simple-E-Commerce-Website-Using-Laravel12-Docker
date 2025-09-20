<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function basic_test_works()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function can_create_user()
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function api_returns_unauthorized_for_protected_route()
    {
        $response = $this->getJson('/api/cart');
        $response->assertStatus(401);
    }
}

