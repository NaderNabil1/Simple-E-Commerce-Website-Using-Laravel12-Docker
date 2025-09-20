<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function first_user_automatically_becomes_admin()
    {
        $user = User::factory()->create();

        $this->assertEquals('admin', $user->role);
    }

    /** @test */
    public function subsequent_users_have_user_role()
    {
        // Create first user (admin)
        User::factory()->create();

        // Create second user
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
    }

    /** @test */
    public function user_can_check_if_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function user_has_many_orders()
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->orders);
        $this->assertTrue($user->orders->contains($order1));
        $this->assertTrue($user->orders->contains($order2));
    }

    /** @test */
    public function user_has_many_assigned_orders()
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->create(['assigned_to' => $user->id]);
        $order2 = Order::factory()->create(['assigned_to' => $user->id]);

        $this->assertCount(2, $user->assignedOrders);
        $this->assertTrue($user->assignedOrders->contains($order1));
        $this->assertTrue($user->assignedOrders->contains($order2));
    }

    /** @test */
    public function user_can_create_api_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }

    /** @test */
    public function user_password_is_hashed()
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    /** @test */
    public function user_email_is_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }
}

