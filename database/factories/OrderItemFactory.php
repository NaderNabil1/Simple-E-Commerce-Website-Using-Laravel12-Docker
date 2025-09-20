<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);

        $onSale = $product->sale_price > 0 && $product->sale_price !== null && $product->sale_end_date !== null && Carbon::parse($product->sale_end_date)->isFuture();

        $price = $onSale ? $product->sale_price : $product->price;
        $oldPrice = $onSale ? $product->price : $price;

        $totalPrice = $price * $quantity;
        $totalOldPrice = $oldPrice * $quantity;
        $discount = $totalOldPrice - $totalPrice;

        return [
            'order_id'        => Order::factory(),
            'product_id'      => $product->id,
            'quantity'        => $quantity,
            'price'           => $price,
            'old_price'       => $oldPrice,
            'total_price'     => $totalPrice,
            'total_old_price' => $totalOldPrice,
            'discount'        => $discount,
        ];
    }
}
