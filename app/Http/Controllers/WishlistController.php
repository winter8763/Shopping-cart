<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Wishlist_item;
use App\Models\Product;

class WishlistController extends Controller
{

    // 顯示使用者的願望清單（預設一個 wishlist）
    public function index(Request $request)
    {
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => 'default']
        );
        $items = $wishlist->items()->with('product')->get();

        return view('wishlist.index', compact('items'));
    }

    // 加入願望清單（如果已存在則回報）
    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => 'default']
        );

        $exists = $wishlist->items()->where('product_id', $request->product_id)->exists();
        if ($exists) {
            return back()->with('error', '商品已在願望清單中');
        }

        $wishlist->items()->create(['product_id' => $request->product_id]);

        return back()->with('success', '已加入願望清單');
    }

    // 從願望清單移除
    public function destroy(Wishlist_item $item)
    {
        if ($item->wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $item->delete();
        return back()->with('success', '已從願望清單移除');
    }

    // 將願望清單項目加入購物車（並移除願望清單項目）
    public function moveToCart(Wishlist_item $item, Request $request)
    {
        if ($item->wishlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $product = $item->product;
        if (! $product) {
            $item->delete();
            return back()->with('error', '商品不存在');
        }

        // 把商品加入 session cart（簡單合併 qty）
        $cart = $request->session()->get('cart', []);

        $found = false;
        // 尋找是否已存在於購物車
        foreach ($cart as &$c) {
            if (($c['id'] ?? null) == $product->id) {
                $c['qty'] = ($c['qty'] ?? 1) + 1;
                $found = true;
                break;
            }
        }
        unset($c);

        // 若不存在則加入新項目
        if (! $found) {
            $cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'qty' => 1,
            ];
        }

        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', '已加入購物車');
    }
}
