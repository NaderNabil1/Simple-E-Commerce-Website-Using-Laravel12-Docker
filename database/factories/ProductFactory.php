<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $hasSale = $this->faker->boolean(40); // 40% chance product has sale
        $salePrice = $hasSale ? $this->faker->randomFloat(2, 5, 50) : null;
        $saleEndDate = $hasSale ? Carbon::now()->addDays(rand(1, 15)) : null;

        return [
            'name'           => $this->faker->words(3, true),
            'description'    => $this->faker->sentence(),
            'price'          => $this->faker->randomFloat(2, 10, 100),
            'sale_price'     => $salePrice,
            'sale_end_date'  => $saleEndDate,
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'status'         => 1,
            'category_id'    => null,
        ];
    }
}
