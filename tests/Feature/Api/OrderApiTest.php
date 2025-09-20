<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->category = Category::factory()->create();
        $this->product1 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 10.00,
        ]);
        $this->product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 20.00,
        ]);
    }

    /** @test */
    public function authenticated_user_can_get_their_orders()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create orders for the user
        $order1 = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'status' => 'pending',
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'status' => 'delivered',
        ]);

        // Create order items
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $this->product1->id,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $this->product2->id,
        ]);

        $response = $this->getJson('/api/orders', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'orders' => [
                    '*' => [
                        'id',
                        'order_code',
                        'total_price',
                        'status',
                        'created_at',
                        'items' => [
                            '*' => [
                                'id',
                                'product_id',
                                'quantity',
                                'price',
                                'product' => [
                                    'id',
                                    'name',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('orders'));
    }

    /** @test */
    public function unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function authenticated_user_can_get_specific_order()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $order = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'order_code' => '12345678',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
        ]);

        $response = $this->getJson('/api/orders/show?order_id=' . $order->id, $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_code',
                    'total_price',
                    'status',
                    'name',
                    'email',
                    'created_at',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'price',
                            'product' => [
                                'id',
                                'name',
                                'price',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'order_code' => '12345678',
                ],
            ]);
    }

    /** @test */
    public function user_cannot_access_other_users_order()
    {
        $userData = $this->createUserWithToken();
        $otherUser = User::factory()->create();
        $headers = $this->getAuthHeaders($userData['token']);

        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson('/api/orders/show?order_id=' . $order->id, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied',
            ]);
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $adminData = $this->createAdminWithToken();
        $headers = $this->getAuthHeaders($adminData['token']);

        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $updateData = [
            'order_id' => $order->id,
            'status' => 'delivered',
        ];

        $response = $this->postJson('/api/orders/change-status', $updateData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order status updated successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    /** @test */
    public function regular_user_cannot_update_order_status()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $updateData = [
            'order_id' => $order->id,
            'status' => 'delivered',
        ];

        $response = $this->postJson('/api/orders/change-status', $updateData, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied',
            ]);
    }

    /** @test */
    public function admin_can_assign_order_to_user()
    {
        $adminData = $this->createAdminWithToken();
        $employee = User::factory()->create(['role' => 'employee']);
        $headers = $this->getAuthHeaders($adminData['token']);

        $order = Order::factory()->create([
            'assigned_to' => null,
        ]);

        $assignData = [
            'order_id' => $order->id,
            'assigned_to' => $employee->id,
        ];

        $response = $this->postJson('/api/orders/assign', $assignData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order assigned successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'assigned_to' => $employee->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_assign_orders()
    {
        $userData = $this->createUserWithToken();
        $employee = User::factory()->create(['role' => 'employee']);
        $headers = $this->getAuthHeaders($userData['token']);

        $order = Order::factory()->create();

        $assignData = [
            'order_id' => $order->id,
            'assigned_to' => $employee->id,
        ];

        $response = $this->postJson('/api/orders/assign', $assignData, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied',
            ]);
    }

    /** @test */
    public function update_order_status_requires_valid_status()
    {
        $adminData = $this->createAdminWithToken();
        $headers = $this->getAuthHeaders($adminData['token']);

        $order = Order::factory()->create();

        $updateData = [
            'order_id' => $order->id,
            'status' => 'invalid_status',
        ];

        $response = $this->postJson('/api/orders/change-status', $updateData, $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function update_order_status_requires_order_id()
    {
        $adminData = $this->createAdminWithToken();
        $headers = $this->getAuthHeaders($adminData['token']);

        $updateData = [
            'status' => 'delivered',
        ];

        $response = $this->postJson('/api/orders/change-status', $updateData, $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    /** @test */
    public function assign_order_requires_valid_user()
    {
        $adminData = $this->createAdminWithToken();
        $headers = $this->getAuthHeaders($adminData['token']);

        $order = Order::factory()->create();

        $assignData = [
            'order_id' => $order->id,
            'assigned_to' => 999, // Non-existent user
        ];

        $response = $this->postJson('/api/orders/assign', $assignData, $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found',
            ]);
    }

    /** @test */
    public function cannot_assign_order_to_nonexistent_order()
    {
        $adminData = $this->createAdminWithToken();
        $employee = User::factory()->create(['role' => 'employee']);
        $headers = $this->getAuthHeaders($adminData['token']);

        $assignData = [
            'order_id' => 999, // Non-existent order
            'assigned_to' => $employee->id,
        ];

        $response = $this->postJson('/api/orders/assign', $assignData, $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found',
            ]);
    }

    /** @test */
    public function get_specific_order_requires_order_id()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->getJson('/api/orders/show', $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    /** @test */
    public function cannot_get_nonexistent_order()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->getJson('/api/orders/show?order_id=999', $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found',
            ]);
    }

    /** @test */
    public function user_orders_are_ordered_by_created_at_desc()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create orders with different timestamps
        $order1 = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'created_at' => now()->subDays(2),
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'created_at' => now()->subDays(1),
        ]);
        $order3 = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/orders', $headers);

        $response->assertStatus(200);

        $orders = $response->json('orders');
        $this->assertEquals($order3->id, $orders[0]['id']);
        $this->assertEquals($order2->id, $orders[1]['id']);
        $this->assertEquals($order1->id, $orders[2]['id']);
    }
}

