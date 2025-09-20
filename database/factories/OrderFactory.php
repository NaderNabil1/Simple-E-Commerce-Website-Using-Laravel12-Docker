<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        do {
            $code = rand(10000000, 99999999);
        } while (Order::where('order_code', $code)->exists());

        $user = User::factory()->create();

        return [
            'user_id'         => $user->id,
            'order_code'      => $code,
            'name'            => $user->name,
            'email'           => $user->email,
            'status'          => 'pending',
            'total_price'     => 0,
            'total_old_price' => 0,
            'discount'        => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }
}
