<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 25.00,
            'stock_quantity' => 50,
        ]);
    }

    /** @test */
    public function authenticated_user_can_get_products()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create additional products
        Product::factory()->count(2)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/products', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'sale_price',
                        'stock_quantity',
                        'status',
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('products'));
    }

    /** @test */
    public function unauthenticated_user_cannot_access_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function authenticated_user_can_get_specific_product()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->getJson('/api/products/' . $this->product->id, $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'product' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'sale_price',
                    'stock_quantity',
                    'status',
                    'category' => [
                        'id',
                        'name',
                    ],
                ],
            ])
            ->assertJson([
                'product' => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->price,
                ],
            ]);
    }

    /** @test */
    public function cannot_get_nonexistent_product()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->getJson('/api/products/999', $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }

    /** @test */
    public function products_can_be_filtered_by_category()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create another category and product
        $category2 = Category::factory()->create();
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->getJson('/api/products?category_id=' . $this->category->id, $headers);

        $response->assertStatus(200);

        $products = $response->json('products');
        foreach ($products as $product) {
            $this->assertEquals($this->category->id, $product['category']['id']);
        }
    }

    /** @test */
    public function products_can_be_searched_by_name()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create product with specific name
        $searchProduct = Product::factory()->create([
            'name' => 'Special Test Product',
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/products?search=Special', $headers);

        $response->assertStatus(200);

        $products = $response->json('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Special Test Product', $products[0]['name']);
    }

    /** @test */
    public function products_can_be_sorted_by_price()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create products with different prices
        Product::factory()->create([
            'name' => 'Cheap Product',
            'price' => 10.00,
            'category_id' => $this->category->id,
        ]);
        Product::factory()->create([
            'name' => 'Expensive Product',
            'price' => 100.00,
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/products?sort=price&order=asc', $headers);

        $response->assertStatus(200);

        $products = $response->json('products');
        $this->assertCount(3, $products);
        $this->assertEquals(10.00, $products[0]['price']);
        $this->assertEquals(25.00, $products[1]['price']);
        $this->assertEquals(100.00, $products[2]['price']);
    }

    /** @test */
    public function products_can_be_paginated()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create many products
        Product::factory()->count(15)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/products?per_page=5&page=1', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);

        $this->assertCount(5, $response->json('products'));
        $this->assertEquals(1, $response->json('pagination.current_page'));
        $this->assertEquals(5, $response->json('pagination.per_page'));
    }

    /** @test */
    public function only_available_products_are_shown()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        // Create unavailable product
        Product::factory()->create([
            'status' => 0, // Unavailable
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/products', $headers);

        $response->assertStatus(200);

        $products = $response->json('products');
        foreach ($products as $product) {
            $this->assertEquals(1, $product['status']); // Only available products
        }
    }

    /** @test */
    public function product_shows_sale_price_when_on_sale()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $saleProduct = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 80.00,
            'sale_end_date' => now()->addDays(7),
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/products/' . $saleProduct->id, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'product' => [
                    'price' => 100.00,
                    'sale_price' => 80.00,
                ],
            ]);
    }

    /** @test */
    public function product_shows_stock_quantity()
    {
        $userData = $this->createUserWithToken();
        $headers = $this->getAuthHeaders($userData['token']);

        $response = $this->getJson('/api/products/' . $this->product->id, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'product' => [
                    'stock_quantity' => 50,
                ],
            ]);
    }
}

