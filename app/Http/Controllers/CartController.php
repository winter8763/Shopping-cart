<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    // 取得或建立該使用者的購物車
    protected function getCartForUser(): Cart
    {
        return Cart::firstOrCreate(['user_id' => auth()->id()]);
    }

    // 顯示購物車
    public function index(Request $request)
    {
        $cart = $this->getCartForUser();
        $items = $cart->items()->with('product')->get();

        $payload = $items->map(function ($it) {
            return [
                'id' => $it->product_id,
                'name' => $it->name,
                'price' => $it->price ?? $it->product->price ?? 0,
                'qty' => (int) $it->quantity,
                'image_url' => $it->product->image_url ?? null,
            ];
        })->all();

        $total = collect($payload)->sum(fn($i) => $i['price'] * $i['qty']);

        return view('cart.index', ['cart' => $payload, 'total' => $total]);
    }

    // 加入購物車（若已有商品則累加數量）
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        $qty = (int) ($data['qty'] ?? 1);
        $product = Product::findOrFail($data['product_id']);

        $cart = $this->getCartForUser();

        DB::transaction(function () use ($cart, $product, $qty) {
            $item = $cart->items()->where('product_id', $product->id)->first();
            if ($item) {
                $item->quantity = $item->quantity + $qty;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                ]);
            }
        });

        return redirect()->back()->with('success', '已加入購物車（儲存於資料庫）。');
    }

    // 更新數量（qty == 0 則刪除該項）
    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:0',
        ]);

        $cart = $this->getCartForUser();
        $item = $cart->items()->where('product_id', $data['product_id'])->first();

        if (! $item) {
            return redirect()->back()->with('error', '商品不存在於購物車。');
        }

        if ($data['qty'] <= 0) {
            $item->delete();
        } else {
            $item->quantity = $data['qty'];
            $item->save();
        }

        return redirect()->back()->with('success', '購物車已更新。');
    }

    // 移除單項
    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $cart = $this->getCartForUser();
        $cart->items()->where('product_id', $data['product_id'])->delete();

        return redirect()->back()->with('success', '已從購物車移除。');
    }

    // 清空購物車
    public function clear(Request $request)
    {
        $cart = $this->getCartForUser();
        $cart->items()->delete();

        return redirect()->route('cart.index')->with('success', '購物車已清空。');
    }
}
