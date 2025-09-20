<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /** @test */
    public function product_has_many_order_items()
    {
        $product = Product::factory()->create();
        $orderItem1 = OrderItem::factory()->create(['product_id' => $product->id]);
        $orderItem2 = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->orderItems);
        $this->assertTrue($product->orderItems->contains($orderItem1));
        $this->assertTrue($product->orderItems->contains($orderItem2));
    }

    /** @test */
    public function product_can_have_sale_price()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 80.00,
        ]);

        $this->assertEquals(100.00, $product->price);
        $this->assertEquals(80.00, $product->sale_price);
    }

    /** @test */
    public function product_can_have_sale_end_date()
    {
        $saleEndDate = now()->addDays(7);
        $product = Product::factory()->create([
            'sale_end_date' => $saleEndDate,
        ]);

        $this->assertEquals($saleEndDate->format('Y-m-d'), $product->sale_end_date->format('Y-m-d'));
    }

    /** @test */
    public function product_has_fillable_attributes()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => 1,
            'price' => 50.00,
            'sale_price' => 40.00,
            'sale_end_date' => now()->addDays(5),
            'stock_quantity' => 100,
            'status' => 1,
        ];

        $product = Product::create($productData);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals(1, $product->category_id);
        $this->assertEquals(50.00, $product->price);
        $this->assertEquals(40.00, $product->sale_price);
        $this->assertEquals(100, $product->stock_quantity);
        $this->assertEquals(1, $product->status);
    }

    /** @test */
    public function product_can_be_created_with_minimal_data()
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->name);
        $this->assertNotNull($product->price);
        $this->assertNotNull($product->stock_quantity);
    }

    /** @test */
    public function product_price_is_decimal()
    {
        $product = Product::factory()->create(['price' => 99.99]);

        $this->assertIsFloat($product->price);
        $this->assertEquals(99.99, $product->price);
    }

    /** @test */
    public function product_stock_quantity_is_integer()
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $this->assertIsInt($product->stock_quantity);
        $this->assertEquals(50, $product->stock_quantity);
    }
}

