<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Notifications\OrderPlaced;
use App\Models\Cart;
use App\Models\CartItem;
use App\Http\Controllers\Controller;


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

    // 從 DB cart 建立訂單（改為使用資料庫購物車）
    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // 取得該使用者的購物車與項目
        $cart = Cart::where('user_id', $user->id)->first();
        $items = $cart->items()->with('product')->get();
        if (! $cart || count($items) === 0) {
            return redirect()->route('cart.index')->with('error', '購物車為空，無法建立訂單。');
        }

        // 驗證可選的配送資訊
        $data = $request->validate([
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $total = $items->sum(fn($i) => ($i->price ?? 0) * (int) ($i->quantity ?? 1));

            $order = new Order();
            $order->user_id = $user->id;
            $order->total_price = $total ?? 0;
            $order->shipping_address = $data['address'] ?? null;
            $order->notes = $data['notes'] ?? null;
            $order->status = 'pending';
            $order->payment_method = 'cash';
            $order->save();

            // $item 是一筆 CartItem model
            foreach ($items as $item) {
                $quantity = (int) ($item->quantity ?? 1);
                $product = $item->product; // eager loaded（預先載入）取得主模型時，一併把關聯資料一次查出

                if ($product) {
                    if ($product->stock < $quantity) {
                        DB::rollBack();
                        return redirect()->route('cart.index')->with('error', "商品 {$product->name} 庫存不足。");
                    }
                    // 原子扣庫存
                    $product->decrement('stock', $quantity);
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'name' => $item->name ?? ($product->name ?? null),
                    'price' => (float) ($item->price ?? ($product->price ?? 0)),
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();

            // 建單成功後清空該使用者的購物車項目
            $cart->items()->delete();

            // 送出通知（email / 簡訊）
            $user->notify(new OrderPlaced($order));

            return redirect()->route('orders.show', $order)->with('success', '訂單已建立。');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Create order failed: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', '建立訂單失敗，請稍後再試。');
        }
    }
}
