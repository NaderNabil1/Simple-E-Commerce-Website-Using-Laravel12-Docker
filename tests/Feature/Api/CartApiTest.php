<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a category and products for testing
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
    public function authenticated_user_can_get_their_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create a cart with items
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'price' => $this->product1->price,
        ]);

        $response = $this->getJson('/api/cart', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cart' => [
                    'id',
                    'user_id',
                    'status',
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
                'total',
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_cart()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function authenticated_user_can_add_item_to_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $cartData = [
            'product_id' => $this->product1->id,
            'quantity' => 3,
        ];

        $response = $this->postJson('/api/cart/add-to-cart', $cartData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item added to cart successfully',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product1->id,
            'quantity' => 3,
            'price' => $this->product1->price,
        ]);
    }

    /** @test */
    public function adding_existing_item_updates_quantity()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create cart and add initial item
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);

        // Add same item again
        $cartData = [
            'product_id' => $this->product1->id,
            'quantity' => 3,
        ];

        $response = $this->postJson('/api/cart/add-to-cart', $cartData, $headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product1->id,
            'quantity' => 5, // 2 + 3
        ]);
    }

    /** @test */
    public function cannot_add_item_with_insufficient_stock()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Update product to have low stock
        $this->product1->update(['stock_quantity' => 2]);

        $cartData = [
            'product_id' => $this->product1->id,
            'quantity' => 5, // More than available stock
        ];

        $response = $this->postJson('/api/cart/add-to-cart', $cartData, $headers);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Insufficient stock',
            ]);
    }

    /** @test */
    public function authenticated_user_can_remove_item_from_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create cart with item
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
        ]);

        $removeData = [
            'product_id' => $this->product1->id,
        ];

        $response = $this->postJson('/api/cart/remove', $removeData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item removed from cart successfully',
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_item_quantity()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create cart with item
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);

        $updateData = [
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ];

        $response = $this->postJson('/api/cart/edit-quantity', $updateData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart updated successfully',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function authenticated_user_can_empty_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create cart with items
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $response = $this->postJson('/api/cart/empty', [], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart emptied successfully',
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_checkout_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create cart with items
        $cart = Cart::factory()->create(['user_id' => $userData['user']->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'price' => $this->product1->price,
        ]);

        $checkoutData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $response = $this->postJson('/api/cart/checkout', $checkoutData, $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_code',
                    'total_price',
                    'status',
                ],
                'message',
            ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $userData['user']->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'pending',
        ]);

        // Verify cart is emptied after checkout
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
    }

    /** @test */
    public function checkout_requires_name_and_email()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->postJson('/api/cart/checkout', [], $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /** @test */
    public function cannot_checkout_empty_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $checkoutData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $response = $this->postJson('/api/cart/checkout', $checkoutData, $headers);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cart is empty',
            ]);
    }

    /** @test */
    public function add_to_cart_requires_product_id_and_quantity()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->postJson('/api/cart/add-to-cart', [], $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    /** @test */
    public function cannot_add_nonexistent_product_to_cart()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $cartData = [
            'product_id' => 999, // Non-existent product
            'quantity' => 1,
        ];

        $response = $this->postJson('/api/cart/add-to-cart', $cartData, $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }
}

