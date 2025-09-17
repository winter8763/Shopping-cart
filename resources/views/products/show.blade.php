<!doctype html>
<html>
<head><meta charset="utf-8"><title>{{ $product->name }}</title></head>
<body>
    <h1>{{ $product->name }}</h1>

    {{-- 訊息 --}}
    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <p>{{ $product->description }}</p>
    <p>Price: ${{ $product->price }}</p>
    <p>Stock: {{ $product->stock }}</p>

    <p>
        <a href="{{ route('products.index') }}">Back</a>
        <a href="{{ route('products.edit', $product) }}" style="margin-left:8px">Edit</a>
    </p>

    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline">
        @csrf @method('DELETE')
        <button>Delete</button>
    </form>

    <!-- 加入購物車表單 -->
    <form action="{{ route('cart.add') }}" method="POST" style="margin-top:12px;">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <label>數量：
            <input type="number" name="qty" value="{{ old('qty', 1) }}" min="1" style="width:60px">
        </label>
        <button type="submit">加入購物車</button>
    </form>

    <!-- 加入願望清單 -->
    @auth
    <form action="{{ route('wishlist.store') }}" method="POST" style="margin-top:8px;">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button type="submit">加入願望清單</button>
    </form>
    @else
    <p><a href="{{ route('login') }}">登入</a> 後可加入願望清單</p>
    @endauth
</body>
</html>