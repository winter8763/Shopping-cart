<!doctype html>
<html>
<head><meta charset="utf-8"><title>訂單 #{{ $order->id }}</title></head>
<body>
    <h1>訂單 #{{ $order->id }}</h1>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <p>狀態：{{ $order->status ?? '無資料' }}</p>
    <p>總計：${{ number_format($order->total_price ?? 0,2) }}</p>
    <p>建立於：{{ $order->created_at ?? '無資料' }}</p>
    <p>地址：{{ $order->shipping_address ?? '無資料' }}</p>
    <p>備註：{{ $order->notes ?? '無資料' }}</p>

    <h2>項目</h2>
    <ul>
    @foreach($order->items as $it)
        <li>{{ $it->name }} × {{ $it->quantity }} - ${{ number_format($it->price,2) }}</li>
    @endforeach
    </ul>

    <p><a href="{{ route('orders.index') }}">回訂單列表</a></p>
    <p><a href="{{ route('home') }}">回商品列表</a></p>
</body>
</html>