<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>首頁 - 商城</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">我的商城</a>
        <div class="ms-auto d-flex align-items-center">
            @php
                $cartCount = 0;
                if (auth()->check()) {
                    $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
                    $cartCount = $cart ? (int) $cart->items()->sum('quantity') : 0;
                } else {
                    $cartCount = collect(session('cart', []))->sum('qty');
                }
            @endphp
            
            <a href="{{ route('wishlist.index') }}" class="btn btn-outline-warning me-2">
                願望清單
                @if(!empty($wishlistCount) && $wishlistCount > 0)
                    <span class="badge bg-danger ms-1">{{ $wishlistCount }}</span>
                @endif
            </a>

            <a href="{{ route('cart.index') }}" class="btn btn-outline-success me-2">
                購物車
                @if($cartCount > 0)
                    <span class="badge bg-danger ms-1">{{ $cartCount }}</span>
                @endif
            </a>

            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">登入</a>
                <a href="{{ url('register') }}" class="btn btn-primary">註冊</a>
            @else
                <a href="{{ route('profile') }}" class="btn btn-outline-secondary me-2">會員資料</a>
                <form method="POST" action="{{ url('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-danger" type="submit">登出</button>
                </form>
            @endguest
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4">商品清單</h1>

    {{-- 搜尋與篩選表單（GET） --}}
    <form method="GET" action="{{ route('home') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label small">關鍵字</label>
            <input type="text" name="search" class="form-control" placeholder="搜尋商品名稱或描述" value="{{ request('search') }}">
        </div>

        <div class="col-auto">
            <label class="form-label small">價格下限</label>
            <input type="number" step="1" name="min_price" class="form-control" placeholder="0" value="{{ request('min_price') }}">
        </div>
        <div class="col-auto">
            <label class="form-label small">價格上限</label>
            <input type="number" step="1" name="max_price" class="form-control" placeholder="9999" value="{{ request('max_price') }}">
        </div>

        <div class="col-auto">
            <label class="form-label small mt-2">排序</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>最新</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>價格：由低到高</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>價格：由高到低</option>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">搜尋</button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">重置</a>
        </div>
    </form>

    @if(isset($products) && $products->count())
        <div class="mb-2 text-muted">共 {{ $products->total() }} 筆結果</div>

        <div class="row g-3">
            @foreach($products as $product)
                <div class="col-md-3">
                    <div class="card h-100">
                        @if(!empty($product->image_url))
                            <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit:cover;height:160px;">
                        @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:160px;">
                                無圖片
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text mb-2 text-muted">NT$ {{ number_format($product->price, 0) }}</p>
                            <p class="mb-2"><small>庫存：{{ $product->stock ?? 0 }}</small></p>

                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">查看商品</a>

                                <form method="POST" action="{{ route('cart.add') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-sm btn-primary">加入購物車</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    @else
        <div class="alert alert-info">目前沒有符合條件的商品。</div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>