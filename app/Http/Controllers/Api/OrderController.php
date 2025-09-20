<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\AuthService;
use Carbon\Carbon;
use App\Models\OrderLog;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;


class OrderController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $orders = Order::with([
            'items' => function ($q) {
                $q->select('id', 'order_id', 'product_id', 'quantity', 'price', 'total_price');
            },
            'items.product:id,name',
        ])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('email', $user->email);
        })->get();

        if ($orders->isNotEmpty()) {
            return response()->json(['orders' => $orders]);
        } else {
            return response()->json(['error' => 'No Orders yet'], 400);
        }
    }

    public function show(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'order_code' => 'required|integer|exists:orders,order_code',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::with([
            'items' => function ($q) {
                $q->select('id', 'order_id', 'product_id', 'quantity', 'price', 'total_price');
            },
            'items.product:id,name',
        ])->where('order_code', $request->header('order_code'))->first();

        if (!$order) {
            return response()->json(['errors' => 'Order not found'], 400);
        }

        return response()->json([
            'order'  => $order,
        ]);
    }


    public function updateStatus(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'order_code' => ['required','integer','exists:orders,order_code'],
            'action'     => ['required', Rule::in(['status','cancel'])],

            'status'      => ['nullable','string', Rule::in(['pending','shipped','delivered'])],
            'description' => ['nullable','string','max:1000'],

            'cancel_description' => ['nullable','string','max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::where('order_code', $request->order_code)->firstOrFail();

        Gate::forUser($user)->authorize(
            $request->action === 'status' ? 'changeStatus' : 'cancel',
            $order
        );

        if ($order->status === 'cancelled') {
            return response()->json(['error' => 'Cancelled orders cannot be modified.'], 422);
        }

        if ($request->action === 'cancel') {
            if ($order->status === 'delivered') {
                return response()->json(['error' => 'Delivered orders cannot be cancelled.'], 422);
            }

            $request->validate([
                'cancel_description' => ['required','string','max:1000'],
            ]);

            DB::transaction(function () use ($order, $user, $request) {
                OrderLog::create([
                    'order_id'    => $order->id,
                    'description' => $request->cancel_description,
                    'status'      => 'cancelled',
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'created_by'  => $user->id,
                ]);

                $order->update(['status' => 'cancelled']);

                $order->loadMissing('items');
                foreach ($order->items as $item) {
                    if ($product = Product::find($item->product_id)) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
            });

            return response()->json(['message' => 'Order canceled successfully.'], 200);
        }

        if ($order->status === 'delivered') {
            return response()->json(['error' => 'Delivered orders cannot be modified.'], 422);
        }

        $request->validate([
            'status'      => ['required','string', Rule::in(['pending','shipped','delivered'])],
            'description' => ['nullable','string','max:1000'],
        ]);

        $allStatuses = ['pending','shipped','delivered'];
        $logged      = OrderLog::where('order_id', $order->id)->pluck('status')->toArray();

        $nextAllowed = null;
        foreach ($allStatuses as $status) {
            if (!in_array($status, $logged, true)) {
                $nextAllowed = $status;
                break;
            }
        }
        if (!$nextAllowed) {
            return response()->json(['error' => 'No further status changes allowed.'], 422);
        }
        if ($request->status !== $nextAllowed) {
            return response()->json([
                'error' => 'Invalid status transition.',
                'details' => [
                    'current_status'   => $order->status,
                    'next_allowed'     => $nextAllowed,
                    'attempted_status' => $request->status,
                ],
            ], 422);
        }

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => $request->status]);

            OrderLog::create([
                'order_id'    => $order->id,
                'description' => $request->description,
                'status'      => $request->status,
                'created_by'  => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
            ]);
        });

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data'    => [
                'order_id' => $order->id,
                'status'   => $order->status,
            ],
        ], 200);
    }

    public function assign(Request $request)
    {
        $user = $this->authService->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'order_code'  => ['required','integer','exists:orders,order_code'],
            'employee_id' => ['required','integer', Rule::exists('users','id')],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::where('order_code', $request->order_code)->firstOrFail();

        Gate::forUser($user)->authorize('assign', $order);

        if (in_array($order->status, ['delivered','cancelled'], true)) {
            return response()->json(['error' => ucfirst($order->status) . ' orders cannot be reassigned.'], 422);
        }

        $employee = User::find($request->employee_id);
        if ($employee->role !== 'employee') {
            return response()->json(['error' => 'Selected user is not an employee.'], 422);
        }

        $order->update(['assigned_to' => $employee->id]);

        return response()->json([
            'message' => 'Order assigned successfully.',
            'data' => [
                'order_id' => $order->id,
                'assigned_to' => [
                    'id'    => $employee->id,
                    'name'  => $employee->name,
                    'email' => $employee->email,
                ],
            ],
        ], 200);
    }

}
