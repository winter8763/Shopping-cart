<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\WishlistItem;
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
    public function destroy(WishlistItem $item)
    {
        if ($item->wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $item->delete();
        return back()->with('success', '已從願望清單移除');
    }
}
