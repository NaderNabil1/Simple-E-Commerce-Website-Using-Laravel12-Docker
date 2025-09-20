<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cart_belongs_to_user()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals($user->id, $cart->user->id);
    }

    /** @test */
    public function cart_has_many_items()
    {
        $cart = Cart::factory()->create();
        $item1 = CartItem::factory()->create(['cart_id' => $cart->id]);
        $item2 = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->assertCount(2, $cart->items);
        $this->assertTrue($cart->items->contains($item1));
        $this->assertTrue($cart->items->contains($item2));
    }

    /** @test */
    public function cart_has_fillable_attributes()
    {
        $cartData = [
            'user_id' => 1,
            'status' => 'active',
        ];

        $cart = Cart::create($cartData);

        $this->assertEquals(1, $cart->user_id);
        $this->assertEquals('active', $cart->status);
    }

    /** @test */
    public function cart_can_have_different_statuses()
    {
        $statuses = ['active', 'checkout', 'completed', 'abandoned'];

        foreach ($statuses as $status) {
            $cart = Cart::factory()->create(['status' => $status]);
            $this->assertEquals($status, $cart->status);
        }
    }

    /** @test */
    public function cart_can_calculate_total()
    {
        $cart = Cart::factory()->create();

        $item1 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 2,
            'price' => 10.00,
        ]);

        $item2 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 1,
            'price' => 20.00,
        ]);

        // Refresh the cart to get updated items
        $cart->refresh();

        $expectedTotal = (2 * 10.00) + (1 * 20.00);
        $this->assertEquals($expectedTotal, $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        }));
    }

    /** @test */
    public function cart_can_calculate_item_count()
    {
        $cart = Cart::factory()->create();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 3,
        ]);

        $cart->refresh();

        $expectedCount = 2 + 3;
        $this->assertEquals($expectedCount, $cart->items->sum('quantity'));
    }

    /** @test */
    public function cart_can_be_created_with_minimal_data()
    {
        $cart = Cart::factory()->create();

        $this->assertNotNull($cart->user_id);
        $this->assertNotNull($cart->status);
    }

    /** @test */
    public function cart_can_be_emptied()
    {
        $cart = Cart::factory()->create();

        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->assertCount(3, $cart->items);

        $cart->items()->delete();
        $cart->refresh();

        $this->assertCount(0, $cart->items);
    }

    /** @test */
    public function cart_can_find_item_by_product_id()
    {
        $cart = Cart::factory()->create();
        $productId = 123;

        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
        ]);

        $foundItem = $cart->items()->where('product_id', $productId)->first();

        $this->assertNotNull($foundItem);
        $this->assertEquals($item->id, $foundItem->id);
    }
}

