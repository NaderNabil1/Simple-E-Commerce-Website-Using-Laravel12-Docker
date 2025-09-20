<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /** @test */
    public function order_belongs_to_handler()
    {
        $handler = User::factory()->create();
        $order = Order::factory()->create(['assigned_to' => $handler->id]);

        $this->assertInstanceOf(User::class, $order->handler);
        $this->assertEquals($handler->id, $order->handler->id);
    }

    /** @test */
    public function order_has_many_items()
    {
        $order = Order::factory()->create();
        $item1 = OrderItem::factory()->create(['order_id' => $order->id]);
        $item2 = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertCount(2, $order->items);
        $this->assertTrue($order->items->contains($item1));
        $this->assertTrue($order->items->contains($item2));
    }

    /** @test */
    public function order_has_fillable_attributes()
    {
        $orderData = [
            'user_id' => 1,
            'assigned_to' => 2,
            'total_price' => 100.00,
            'total_old_price' => 120.00,
            'discount' => 20.00,
            'quantity' => 2,
            'status' => 'pending',
            'order_code' => '12345678',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $order = Order::create($orderData);

        $this->assertEquals(1, $order->user_id);
        $this->assertEquals(2, $order->assigned_to);
        $this->assertEquals(100.00, $order->total_price);
        $this->assertEquals(120.00, $order->total_old_price);
        $this->assertEquals(20.00, $order->discount);
        $this->assertEquals(2, $order->quantity);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('12345678', $order->order_code);
        $this->assertEquals('John Doe', $order->name);
        $this->assertEquals('john@example.com', $order->email);
    }

    /** @test */
    public function order_can_have_different_statuses()
    {
        $statuses = ['pending', 'processing', 'delivering', 'delivered', 'cancelled'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($status, $order->status);
        }
    }

    /** @test */
    public function order_can_be_created_without_assigned_handler()
    {
        $order = Order::factory()->create(['assigned_to' => null]);

        $this->assertNull($order->assigned_to);
        $this->assertNull($order->handler);
    }

    /** @test */
    public function order_total_price_is_decimal()
    {
        $order = Order::factory()->create(['total_price' => 99.99]);

        $this->assertIsFloat($order->total_price);
        $this->assertEquals(99.99, $order->total_price);
    }

    /** @test */
    public function order_quantity_is_integer()
    {
        $order = Order::factory()->create(['quantity' => 5]);

        $this->assertIsInt($order->quantity);
        $this->assertEquals(5, $order->quantity);
    }

    /** @test */
    public function order_code_is_unique()
    {
        Order::factory()->create(['order_code' => '12345678']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Order::factory()->create(['order_code' => '12345678']);
    }

    /** @test */
    public function order_can_calculate_total_from_items()
    {
        $order = Order::factory()->create(['total_price' => 0]);

        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'price' => 10.00,
        ]);

        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'price' => 20.00,
        ]);

        // Refresh the order to get updated items
        $order->refresh();

        $expectedTotal = (2 * 10.00) + (1 * 20.00);
        $this->assertEquals($expectedTotal, $order->items->sum(function ($item) {
            return $item->quantity * $item->price;
        }));
    }
}

