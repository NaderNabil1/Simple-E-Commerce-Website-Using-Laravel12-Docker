<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin', 'email' => 'admin@blueholding.com',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);

        User::factory()->count(10)->create();

        Category::factory(3)->create()->each(function ($category) {
            Product::factory(5)->create([
                'category_id' => $category->id,
            ]);
        });

       Order::factory(10)->create()->each(function ($order) {

            $products = Product::inRandomOrder()->take(rand(1, 3))->get();

            $totalQuantity    = 0;
            $orderTotalPrice  = 0;
            $orderTotalOld    = 0;
            $orderDiscount    = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $totalQuantity += $quantity;

                $onSale = $product->sale_price > 0 && $product->sale_price !== null && $product->sale_end_date !== null && Carbon::parse($product->sale_end_date)->isFuture();

                $price = $onSale ? $product->sale_price : $product->price;
                $oldPrice = $onSale ? $product->price : $price;

                $totalPrice = $price * $quantity;
                $totalOldPrice = $oldPrice * $quantity;
                $discount = $totalOldPrice - $totalPrice;

                $orderTotalPrice += $totalPrice;
                $orderTotalOld   += $totalOldPrice;
                $orderDiscount   += $discount;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product->id,
                    'quantity'        => $quantity,
                    'price'           => $price,
                    'old_price'       => $oldPrice,
                    'total_price'     => $totalPrice,
                    'total_old_price' => $totalOldPrice,
                    'discount'        => $discount,
                ]);

                OrderLog::create([
                    'order_id'   => $order->id,
                    'description'=> 'Order created and pending',
                    'status'     => 'pending',
                    'created_by' => $order->user_id,
                    'name'       => $order->createdBy?->name,
                    'email'      => $order->createdBy?->email,
                ]);
            }


            $order->update([
                'quantity'        => $totalQuantity,
                'total_price'     => $orderTotalPrice,
                'total_old_price' => $orderTotalOld,
                'discount'        => $orderDiscount,
            ]);
        });
    }

}
