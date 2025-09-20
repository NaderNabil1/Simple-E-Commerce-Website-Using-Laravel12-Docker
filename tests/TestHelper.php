<?php

namespace Tests;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;

class TestHelper
{
    /**
     * Create a complete test environment with sample data
     */
    public static function createTestEnvironment(): array
    {
        // Create category
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'status' => 1,
        ]);

        // Create products
        $product1 = Product::factory()->create([
            'name' => 'Test Product 1',
            'price' => 25.00,
            'sale_price' => 20.00,
            'stock_quantity' => 100,
            'category_id' => $category->id,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'price' => 50.00,
            'stock_quantity' => 50,
            'category_id' => $category->id,
        ]);

        // Create users
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        return [
            'category' => $category,
            'products' => [$product1, $product2],
            'admin' => $admin,
            'user' => $user,
        ];
    }

    /**
     * Create a cart with items for testing
     */
    public static function createCartWithItems(User $user, array $products): Cart
    {
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        foreach ($products as $index => $product) {
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $index + 1,
                'price' => $product->price,
            ]);
        }

        return $cart;
    }

    /**
     * Create an order with items for testing
     */
    public static function createOrderWithItems(User $user, array $products): Order
    {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        foreach ($products as $index => $product) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $index + 1,
                'price' => $product->price,
            ]);
        }

        return $order;
    }

    /**
     * Get authentication headers for API requests
     */
    public static function getAuthHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create user with token
     */
    public static function createUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Create admin with token
     */
    public static function createAdminWithToken(): array
    {
        return self::createUserWithToken(['role' => 'admin']);
    }

    /**
     * Assert JSON response structure
     */
    public static function assertJsonStructure($response, array $structure): void
    {
        $response->assertJsonStructure($structure);
    }

    /**
     * Assert database has record
     */
    public static function assertDatabaseHas(string $table, array $data): void
    {
        \Illuminate\Support\Facades\DB::table($table)->where($data)->exists();
    }

    /**
     * Assert database missing record
     */
    public static function assertDatabaseMissing(string $table, array $data): void
    {
        \Illuminate\Support\Facades\DB::table($table)->where($data)->doesntExist();
    }
}

