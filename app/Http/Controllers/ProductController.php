<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Middleware\IsAdmin;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    private function getCategories()
    {
        return Category::all();
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $min = $request->get('min_price');
        $max = $request->get('max_price');
        $sort = $request->get('sort');

        $query = Product::query(); // 建立查詢（還沒跑SQL）

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
        $categories = $this->getCategories();
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

        $product = new Product();
        $product->name = $data['name'];
        $product->description = $data['description'] ?? '';
        $product->price = $data['price'];
        $product->stock = $data['stock'];
        $product->category_id = $data['category_id'] ?? null;
        $product->image_url = $data['image_url'] ?? '';
        $product->save();

        return redirect()->route('home')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = $this->getCategories();
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

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('home')->with('error', '找不到指定的商品。');
        }

        $product->delete();

        // redirect 時只傳 id（route helper 會用 id 生成 url）
        return redirect()->route('home')->with('success', 'Product deleted.');
    }
}
