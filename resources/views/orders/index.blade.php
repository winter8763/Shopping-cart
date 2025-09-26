<!doctype html>
<html>
<head><meta charset="utf-8"><title>我的訂單</title></head>
<body>
    <h1>訂單列表</h1>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    @if($orders->count())
        <ul>
            @foreach($orders as $o)
                <li>
                    <a href="{{ route('orders.show', $o) }}">訂單 #{{ $o->id }}</a>
                    - {{ $o->status }} - 總計 ${{ number_format($o->total_price,2) }}
                    - 建立於 {{ $o->created_at }}
                </li>
            @endforeach
        </ul>

        {{ $orders->links() }}
    @else
        <p>目前沒有訂單。</p>
    @endif

    <p><a href="{{ route('home') }}">回商品列表</a></p>
</body>
</html>