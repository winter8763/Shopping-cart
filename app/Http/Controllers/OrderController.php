<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use App\Notifications\OrderPlaced;


class OrderController extends Controller
{

    // 使用者的訂單列表（admin 可看全部）
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            $orders = Order::latest()->paginate(20);
        } else {
            $orders = Order::where('user_id', $user->id)->latest()->paginate(20);
        }

        return view('orders.index', compact('orders'));
    }

    // 顯示單筆訂單（包含 items）
    public function show(Order $order)
    {
        // 權限檢查：非 admin 者只能看自己的訂單
        if (auth()->user()->role !== 'admin' && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');
        return view('orders.show', compact('order'));
    }

    // 從 session cart 建立訂單
    public function store(Request $request)
    {

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', '購物車為空，無法建立訂單。');
        }

        // 驗證可選的配送資訊
        $data = $request->validate([
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $total = collect($cart)->sum(fn($i) => (float) ($i['price'] ?? 0) * (int) ($i['qty'] ?? $i['quantity'] ?? 1));

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_price' => $total ?? 0,
                'shipping_address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'payment_method' => 'cash',
            ]);

            foreach ($cart as $item) {
                $quantity = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                $productId = $item['id'] ?? null;

                // 取得 product，若存在則檢查並扣庫存
                $product = $productId ? Product::find($productId) : null;
                if ($product) {
                    if ($product->stock < $quantity) {
                        DB::rollBack();
                        return redirect()->route('cart.index')->with('error', "商品 {$product->name} 庫存不足。");
                    }
                    // 使用 decrement 做原子扣款
                    $product->decrement('stock', $quantity);
                }

                $order->items()->create([
                    'product_id' => $productId,
                    'name' => $item['name'] ?? null,
                    'price' => (float) ($item['price'] ?? 0),
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();

            // 建單成功後清空 session cart
            $request->session()->forget('cart');

            // 送出通知（email / 簡訊）
            if ($request->user()) {
                $request->user()->notify(new OrderPlaced($order));
            }

            return redirect()->route('orders.show', $order)->with('success', '訂單已建立。');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Create order failed: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', '建立訂單失敗，請稍後再試。');
        }
    }
}
