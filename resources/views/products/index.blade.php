<!doctype html>
<html>
<head><meta charset="utf-8"><title>商品列表</title></head>
<body>
    <h1>商品列表</h1>

    <form method="GET" action="{{ route('products.index') }}" style="margin-bottom:12px;">
        <input type="text" name="search" placeholder="關鍵字" value="{{ old('search', $search ?? '') }}">
        <select name="category_id">
            <option value="">全部分類</option>
            @foreach($category as $c)
                <option value="{{ $c->id }}" {{ (string)($categoryId ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <input type="number" name="min_price" step="0.01" placeholder="最低價" value="{{ old('min_price', $min ?? '') }}">
        <input type="number" name="max_price" step="0.01" placeholder="最高價" value="{{ old('max_price', $max ?? '') }}">
        <select name="sort">
            <option value="">排序（預設：最新）</option>
            <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>價格：由低到高</option>
            <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>價格：由高到低</option>
        </select>
        <button type="submit">搜尋 / 篩選</button>
        <a href="{{ route('products.index') }}">重設</a>
    </form>

    @if($products->count())
        <ul>
            @foreach($products as $p)
                <li>
                    <a href="{{ route('products.show', $p) }}">{{ $p->name }}</a>
                    - ${{ number_format($p->price, 2) }}
                </li>
            @endforeach
        </ul>

        {{ $products->links() }}
    @else
        <p>找不到商品。</p>
    @endif

    <p><a href="{{ route('cart.index') }}">前往購物車</a></p>
</body>
</html>