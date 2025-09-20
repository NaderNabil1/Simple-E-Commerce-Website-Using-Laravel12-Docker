<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        return view('Dashboard.Order.index', compact('orders'));
    }

    public function edit(request $request, $id)
    {
        $order = Order::findorfail($id);
        $order_items = OrderItem::where('order_id', $id)->get();
        $order_logs = OrderLog::where('order_id', $id)->get();

        $loggedStatuses = $order_logs->pluck('status')->toArray();
        $allStatuses = ['pending', 'shipped', 'delivered'];
        $nextStatus = null;

        foreach ($allStatuses as $status) {
            if (!in_array($status, $loggedStatuses)) {
                $nextStatus = $status;
                break;
            }
        }

        $employees = User::where('role','employee')->select('id','name','email')->orderBy('name')->get();

        if ($request->isMethod('post')) {

            if (!Auth::user()->role == 'admin') {
                return back()->with('error', 'Only admins can modify this order.');
            }

            $request->validate([
                'status' => 'nullable|string|in:pending,shipped,delivered',
            ]);

            if ($request->action === 'cancel') {
                $request->validate([
                    'cancel_description' => 'required|string|max:1000',
                ]);

                if ($order->status === 'cancelled') {
                    return back()->with('error', 'Order already canceled.');
                }

                OrderLog::create([
                    'order_id' => $order->id,
                    'description' => $request->cancel_description,
                    'status' => 'cancelled',
                    'name' => Auth::User()->name,
                    'email' => Auth::User()->email,
                    'created_by' => auth()->id()
                ]);

                $order->status = 'cancelled';
                $order->save();

                if ($order->items->count() > 0) {
                    foreach ($order->items as $item) {
                        $product = Product::find($item->product_id);
                        if ($product){
                            $product->stock_quantity += $item->quantity;
                            $product->save();
                        }
                    }
                }

                return back()->with('success', 'Order canceled successfully.');
            }

            if ($request->action === 'assign') {
                $request->validate([
                    'employee_id' => [ 'required', 'integer', Rule::exists('users', 'id')]
                ]);

                $employee = User::find($request->employee_id);
                if ($employee->role !== 'employee'){
                    return back()->with('error', 'Selected user is not an employee.');
                }

                if (in_array($order->status, ['delivered','cancelled'])) {
                    return back()->with('error', ucfirst($order->status).' orders cannot be reassigned.');
                }

                $order->assigned_to = $employee->id;
                $order->save();

                return back()->with('success', 'Order assigned to '.$employee->name.' successfully.');
            }

            if ($order->status === 'delivered') {
                return back()->with('error','delivered orders cannot be modified.');
            }

            $order->update([
                'status' => $request->status,
            ]);

            OrderLog::create([
                'order_id' => $order->id,
                'description' => $request->description,
                'status' => $request->status,
                'created_by' => Auth::id(),
                'name' => Auth::User()->name,
                'email' => Auth::User()->email,
            ]);

            return redirect(route('edit-order', $order->id))->with('success', 'Order Edited Successfully!');
        }
        return view('Dashboard.Order.edit', compact('order', 'order_items', 'order_logs','nextStatus','employees'));
    }

    public function delete($id)
    {
        $order = Order::find($id);
        $order_items = OrderItem::where('order_id', $id)->get();
        if ($order->status === 'delivered') {
            return back()->with('error', 'delivered orders cannot be deleted!');
        }
        if (Auth::user()->role == 'admin') {
            $order->delete();
        } else {
            return back()->with('error', 'Not Authorized to preform this action');
        }
        return back()->with('success', 'Order Deleted Successfully!');
    }
}
