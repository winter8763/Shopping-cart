<!doctype html>
<html>
<head><meta charset="utf-8"><title>我的願望清單</title></head>
<body>
    <h1>我的願望清單</h1>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    @if($items->count())
        <ul>
            @foreach($items as $it)
                <li>
                    @if($it->product)
                        <a href="{{ route('products.show', $it->product) }}">{{ $it->product->name }}</a>
                        - ${{ number_format($it->product->price, 2) }}
                        <form action="{{ route('wishlist.moveToCart', $it) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit">加入購物車</button>
                        </form>

                        <form action="{{ route('wishlist.destroy', $it) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit">移除</button>
                        </form>
                    @else
                        <span>（商品已下架）</span>
                        <form action="{{ route('wishlist.destroy', $it) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit">刪除</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>願望清單目前沒有商品。</p>
    @endif

    <p><a href="{{ route('products.index') }}">回商品列表</a></p>
    <p><a href="{{ route('cart.index') }}">前往購物車</a></p>
</body>
</html>