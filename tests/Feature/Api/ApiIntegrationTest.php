<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiIntegrationTest extends TestCase
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
            'stock_quantity' => 100,
        ]);
        $this->product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 20.00,
            'stock_quantity' => 50,
        ]);
    }

    /** @test */
    public function complete_ecommerce_workflow()
    {
        // 1. Register a new user
        $registerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/register', $registerData);
        $registerResponse->assertStatus(201);

        $token = $registerResponse->json('token');
        $headers = $this->getAuthHeaders($token);

        // 2. Get products
        $productsResponse = $this->getJson('/api/products', $headers);
        $productsResponse->assertStatus(200);
        $this->assertCount(2, $productsResponse->json('products'));

        // 3. Add items to cart
        $addToCartData1 = [
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ];
        $addToCartResponse1 = $this->postJson('/api/cart/add-to-cart', $addToCartData1, $headers);
        $addToCartResponse1->assertStatus(200);

        $addToCartData2 = [
            'product_id' => $this->product2->id,
            'quantity' => 1,
        ];
        $addToCartResponse2 = $this->postJson('/api/cart/add-to-cart', $addToCartData2, $headers);
        $addToCartResponse2->assertStatus(200);

        // 4. View cart
        $cartResponse = $this->getJson('/api/cart', $headers);
        $cartResponse->assertStatus(200);
        $this->assertCount(2, $cartResponse->json('cart.items'));

        // 5. Update cart item quantity
        $updateQuantityData = [
            'product_id' => $this->product1->id,
            'quantity' => 3,
        ];
        $updateResponse = $this->postJson('/api/cart/edit-quantity', $updateQuantityData, $headers);
        $updateResponse->assertStatus(200);

        // 6. Checkout cart
        $checkoutData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
        $checkoutResponse = $this->postJson('/api/cart/checkout', $checkoutData, $headers);
        $checkoutResponse->assertStatus(200);

        $orderId = $checkoutResponse->json('order.id');
        $this->assertNotNull($orderId);

        // 7. View orders
        $ordersResponse = $this->getJson('/api/orders', $headers);
        $ordersResponse->assertStatus(200);
        $this->assertCount(1, $ordersResponse->json('orders'));

        // 8. View specific order
        $orderResponse = $this->getJson('/api/orders/show?order_id=' . $orderId, $headers);
        $orderResponse->assertStatus(200);
        $this->assertEquals($orderId, $orderResponse->json('order.id'));

        // 9. Verify cart is empty after checkout
        $emptyCartResponse = $this->getJson('/api/cart', $headers);
        $emptyCartResponse->assertStatus(200);
        $this->assertCount(0, $emptyCartResponse->json('cart.items'));

        // 10. Logout
        $logoutResponse = $this->postJson('/api/logout', [], $headers);
        $logoutResponse->assertStatus(200);
    }

    /** @test */
    public function admin_can_manage_orders()
    {
        // Create admin user
        $adminData = $this->createAdminWithToken();
        $adminHeaders = $this->getAuthHeaders($adminData['token']);

        // Create regular user and order
        $userData = $this->createUserWithToken();
        $order = Order::factory()->create([
            'user_id' => $userData['user']->id,
            'status' => 'pending',
        ]);

        // Admin can update order status
        $updateStatusData = [
            'order_id' => $order->id,
            'status' => 'delivered',
        ];
        $updateResponse = $this->postJson('/api/orders/change-status', $updateStatusData, $adminHeaders);
        $updateResponse->assertStatus(200);

        // Admin can assign order
        $assignData = [
            'order_id' => $order->id,
            'assigned_to' => $adminData['user']->id,
        ];
        $assignResponse = $this->postJson('/api/orders/assign', $assignData, $adminHeaders);
        $assignResponse->assertStatus(200);

        // Verify order was updated
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
            'assigned_to' => $adminData['user']->id,
        ]);
    }

    /** @test */
    public function user_cannot_access_other_users_data()
    {
        // Create two users
        $user1Data = $this->createUserWithToken();
        $user2Data = $this->createUserWithToken();

        // Create order for user1
        $order = Order::factory()->create(['user_id' => $user1Data['user']->id]);

        // User2 tries to access user1's order
        $user2Headers = $this->getAuthHeaders($user2Data['token']);
        $response = $this->getJson('/api/orders/show?order_id=' . $order->id, $user2Headers);
        $response->assertStatus(403);

        // User2 tries to access user1's cart (should be empty for user2)
        $cartResponse = $this->getJson('/api/cart', $user2Headers);
        $cartResponse->assertStatus(200);
        $this->assertCount(0, $cartResponse->json('cart.items'));
    }

    /** @test */
    public function stock_management_works_correctly()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create product with limited stock
        $limitedProduct = Product::factory()->create([
            'stock_quantity' => 2,
            'category_id' => $this->category->id,
        ]);

        // Add more items than available stock
        $addToCartData = [
            'product_id' => $limitedProduct->id,
            'quantity' => 5, // More than available stock
        ];
        $response = $this->postJson('/api/cart/add-to-cart', $addToCartData, $headers);
        $response->assertStatus(400)
            ->assertJson(['message' => 'Insufficient stock']);

        // Add available quantity
        $addToCartData = [
            'product_id' => $limitedProduct->id,
            'quantity' => 2, // Exact available stock
        ];
        $response = $this->postJson('/api/cart/add-to-cart', $addToCartData, $headers);
        $response->assertStatus(200);

        // Try to add more (should fail)
        $addToCartData = [
            'product_id' => $limitedProduct->id,
            'quantity' => 1,
        ];
        $response = $this->postJson('/api/cart/add-to-cart', $addToCartData, $headers);
        $response->assertStatus(400)
            ->assertJson(['message' => 'Insufficient stock']);
    }

    /** @test */
    public function authentication_tokens_work_correctly()
    {
        // Register user
        $registerData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/register', $registerData);
        $token = $registerResponse->json('token');

        // Use token for authenticated request
        $headers = $this->getAuthHeaders($token);
        $response = $this->getJson('/api/cart', $headers);
        $response->assertStatus(200);

        // Logout (revoke token)
        $logoutResponse = $this->postJson('/api/logout', [], $headers);
        $logoutResponse->assertStatus(200);

        // Try to use revoked token
        $response = $this->getJson('/api/cart', $headers);
        $response->assertStatus(401);
    }
}

