<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService )
    {
        $this->authService = $authService;
    }

    protected function user_cart(int $user_id): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user_id, 'status' => 1]);
    }

    public function cart(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $cart = Cart::with('items')->where(['user_id' => $user->id, 'status' => 1])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ['message' => 'Cart is empty'];
        }

        $this->stock_check($cart->items);

        $items = $this->format_items($cart->items);

        $total = 0;
        $old_total = 0;
        $cart_count = 0;

        foreach ($items as $item) {
            $total += $item['total'] ;
            $old_total += $item['old_total'] ;
        }

        $discount = $old_total - $total;
        $cart_count = $items->count();

        return response()->json(['cart' => $items , 'total' => $total , 'old_total' => $old_total , 'discount' => $discount  , 'cart_count' => $cart_count]);
    }

    public function add_to_cart(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $product = Product::where('id', $request->product_id)->firstOrFail();
        if(!$product){
            return response()->json(['error' => 'Product not found'], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $check_product = $this->check_single_stock($request);
        if (isset($check_product['error'])) {
            return response()->json(['error' => $check_product['error']], 403);
        }

        $cart_item = CartItem::firstOrNew(['cart_id' => $this->user_cart($user->id)->id, 'product_id' => $product->id ]);
        $cart_item->quantity += $request->quantity;
        $cart_item->save();

        return response()->json(['message' => 'Product added to cart successfully ']);
    }

    public function edit_quantity(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'quantity'   => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $item = CartItem::where(['cart_id' => $this->user_cart($user->id)->id, 'id' => $request->item])->first();
        if (!$item) {
            return response()->json(['error' => 'Product not found in cart'],403);
        }else{
            $item->quantity = $request->quantity;
            $item->save();
            return response()->json(['message' => 'Product quantity edited successfully ']);
        }
    }

    public function remove_from_cart(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $item = CartItem::where(['cart_id' => $this->user_cart($user->id)->id, 'id' => $request->item])->first();
        if (!$item) {
            return response()->json(['error' => 'Cart item not found in cart'],403);
        }else{
            $item->delete();
            return response()->json(['message' => 'Cart Item deleted successfully ']);
        }
    }


    public function empty_cart(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->user_cart($user->id)->items()->delete();
        return response()->json(['message' => 'Cart Items deleted Successfully'], 200);
    }



    public function checkout(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::with('items')->where(['user_id' => $user->id, 'status' => 1])->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is Empty'], 403);
        }

        $items = $this->format_items($cart->items);

        $total = 0;
        $old_total = 0;
        $quantities = 0;

        foreach ($items as $item) {
            $total += $item['total'];
            $old_total += $item['old_total'];
            $quantities += $item['quantity'];
        }

        $discount = $old_total - $total;

        $uniqueCode = $this->generateUniqueCode();

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id'            => $user->id,
                'order_code'         => $uniqueCode,
                'total_price'        => $total,
                'total_old_price'    => $old_total,
                'quantity'           => $quantities,
                'discount'           => $discount,
                'name'               => $user->name,
                'email'              => $user->email,
                'status'             => 'pending',
            ]);

            $order_items = [];
            foreach ($items as $item) {
                $order_item = OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item['product_id'] ?? null,
                    'quantity'         => $item['quantity'],
                    'price'            => $item['sale_price'] ?? $item['price'],
                    'old_price'        => $item['price'],
                    'total_price'      => $item['total'],
                    'total_old_price'  => $item['old_total'],
                    'discount'         => $item['discount']
                ]);

                if ($item['product_id']) {
                    $product = Product::find($item['product_id']);
                    $product->stock_quantity -= $item['quantity'];
                    if ($product->stock_quantity < 0) {
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'You are ordering a product out of stock'], 422);
                    }
                    $product->save();
                }

                unset($order_item->created_at, $order_item->updated_at);
                $order_items[] = $order_item;
            }

            OrderLog::create([
                'user_id'        => $user->id,
                'order_id'       => $order->id,
                'description' => 'Order created and pending',
                'status'      => 'pending',
                'created_by'  => $user->id,
                'name'  => $user->name,
                'email'       => $user->email,
            ]);

             $cart->update(['status' => 0]);

            DB::commit();

            unset($order->created_at, $order->updated_at);
            return ['order' => $order, 'order_items' => $order_items, 'status' => 'success'];

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Something went wrong', 'debug' => $e->getMessage()], 500);
        }
    }

    public function stock_check($items)
    {
        foreach ($items as $item) {
            if($item->product_id){
                $product = Product::find($item->product_id);
                if (!$product) {
                    $item->stock = false;
                    continue;
                }
            }
        }
    }

    private function format_items($items)
    {
        return $items->map(function ($item){
            $product = Product::find($item->product_id);
            $stock_row = false;

            $price = (float) $product->price;
            $sale_price = null;
            $stock_number = (int) ($product->getAttributes()['stock_quantity'] ?? 0);

            if ($product->sale_price && $product->sale_end_date && now()->lt($product->sale_end_date)) {
                $sale_price = (float) $product->sale_price;
            }

            $stock = $stock_number > 0;

            $total = $sale_price !== null ? $item->quantity * $sale_price : $item->quantity * $price;

            $old_total = $item->quantity * $price;

            return [
                'id' => $item->id,
                'product_name' => $product->name,
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'stock' => $stock,
                'price' => $price,
                'sale_price' => $sale_price,
                'total' => $total,
                'old_total' => $old_total,
                'discount' => $old_total - $total,
            ];
        });
    }


    public function check_single_stock($request)
    {
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ;
        $stock = $product->stock_quantity;

        if($stock == 0){
            return ['error' => 'Product is out of stock'];
        }elseif($quantity > $stock  ){
            return ['error' => 'Only available quantity in stock is ' . $stock ];
        }
    }

    function generateUniqueCode() {

        do {
            $code = rand(10000000, 99999999);
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
