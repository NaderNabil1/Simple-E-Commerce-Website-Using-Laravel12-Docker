<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Category;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::count();
        $products = Product::count();
        $carts = Cart::count();
        $categories_count = Category::count();
        $revenue = Order::where('status','delivered')->sum('total_price');
        $sold_products = OrderItem::sum('quantity');
        $users = User::where('role','user')->count();
        $success_orders = Order::where('status','delivered')->count();
        $pending_orders = Order::where('status', 'pending')->count();
        $cancelled_orders = Order::where('status', 'cancelled')->count();
        $best_selling = OrderItem::select('product_id', DB::raw('COUNT(*) as product_count'))->groupBy('product_id')->orderByDesc('product_count')->limit(10)->pluck('product_id');
        if ($best_selling->isNotEmpty()) {
            $best_selling_products = Product::whereIn('id', $best_selling)->get();
            $best_selling_categories_ids = $best_selling_products->pluck('category_id')->unique();
            $best_selling_categories = Category::whereIn('id', $best_selling_categories_ids)->get();
        } else {
            $best_selling_products = collect();
            $best_selling_categories = collect();
        }


        $this_month_orders_success = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->where('status','delivered')->count();
        $this_month_orders_pending = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->where('status', 'pending')->orWhere('status','delivering')->count();
        $this_month_orders_cancelled = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->where('status', 'cancelled')->count();
        $this_month_revenue = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->where('status','delivered')->sum('total_price');
        $best_selling_monthly = OrderItem::select('product_id', DB::raw('COUNT(*) as product_count'))->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->groupBy('product_id')->orderByDesc('product_count')->limit(5)->pluck('product_id');
        if ($best_selling_monthly->isNotEmpty()) {
            $best_selling_products_monthly = Product::whereIn('id', $best_selling_monthly)->get();
            $best_selling_categories_ids_monthly = $best_selling_products_monthly->pluck('category_id')->unique();
            $best_selling_categories_monthly = Category::whereIn('id', $best_selling_categories_ids_monthly)->get();
        } else {
            $best_selling_products_monthly = collect();
            $best_selling_categories_monthly = collect();
        }

        $currentMonth = now()->month;
        $months = collect(range(1, $currentMonth))->map(function ($month) {
            return (object) [
                'month' => $month,
                'total_orders' => 0,
                'total_revenue' => 0,
            ];
        });
        $actualData = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total_orders, SUM(total_price) as total_revenue')->where('status', 'delivered')->whereYear('created_at', now()->year)->groupBy('month')->orderBy('month')->get();
        $monthlyData = $months->map(function ($defaultMonth) use ($actualData) {
            $found = $actualData->firstWhere('month', $defaultMonth->month);
            return (object) [
                'month' => $defaultMonth->month,
                'total_orders' => $found->total_orders ?? 0,
                'total_revenue' => $found->total_revenue ?? 0,
            ];
        });

        return view('Dashboard.index',
        compact('orders','users','products','revenue','categories_count','carts','success_orders','success_orders', 'pending_orders' , 'cancelled_orders' ,'sold_products',
        'best_selling_products','best_selling_categories','this_month_orders_success','this_month_orders_pending','this_month_orders_cancelled','this_month_revenue','best_selling_products_monthly','best_selling_categories_monthly','monthlyData'));
    }
}
