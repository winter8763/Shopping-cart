<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    protected $sessionKey = 'cart';

    public function index(Request $request)
    {
        $cart = $request->session()->get($this->sessionKey, []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['qty']);
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        $qty = $request->input('qty', 1);
        $product = Product::findOrFail($data['product_id']);

        $cart = $request->session()->get($this->sessionKey, []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $qty;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'qty' => $qty,
                'image_url' => $product->image_url ?? null,
            ];
        }

        $request->session()->put($this->sessionKey, $cart);

        return redirect()->back()->with('success', '已加入購物車。');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:0',
        ]);

        $cart = $request->session()->get($this->sessionKey, []);

        if (! isset($cart[$data['product_id']])) {
            return redirect()->back()->with('error', '商品不存在於購物車。');
        }

        if ($data['qty'] <= 0) {
            unset($cart[$data['product_id']]);
        } else {
            $cart[$data['product_id']]['qty'] = $data['qty'];
        }

        $request->session()->put($this->sessionKey, $cart);

        return redirect()->back()->with('success', '購物車已更新。');
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $cart = $request->session()->get($this->sessionKey, []);

        if (isset($cart[$data['product_id']])) {
            unset($cart[$data['product_id']]);
            $request->session()->put($this->sessionKey, $cart);
        }

        return redirect()->back()->with('success', '已從購物車移除。');
    }

    public function clear(Request $request)
    {
        $request->session()->forget($this->sessionKey);
        return redirect()->route('cart.index')->with('success', '購物車已清空。');
    }
}
