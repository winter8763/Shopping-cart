<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Middleware\IsAdmin;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // query()開始一個查詢
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $sort = $request->query('sort');

        $query = Product::query();

        // 關鍵字
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // 對 name 或 description 做模糊搜尋
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // 分類
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // 價格區間
        if (is_numeric($min)) {
            $query->where('price', '>=', $min);
        }
        if (is_numeric($max)) {
            $query->where('price', '<=', $max);
        }

        // 排序
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest(); // 預設依 created_at 倒序
        }

        $products = $query->paginate(12)->appends($request->query());

        // categories 用於下拉篩選（若沒有 categories table 則回空集合）
        $categories = Schema::hasTable('categories') ? Category::all() : collect();

        return view('welcome', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|url',
        ]);

        $data['category_id'] = $request->input('category_id', null);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|url',
        ]);

        $data['category_id'] = $request->input('category_id', null);

        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        // redirect 時只傳 id（route helper 會用 id 生成 url）
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
