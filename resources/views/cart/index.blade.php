<!doctype html>
<html>
<head><meta charset="utf-8"><title>購物車</title></head>
<body>
    <h1>購物車</h1>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @if(empty($cart) || count($cart) === 0)
        <p>購物車目前為空。</p>
    @else
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr><th>商品</th><th>單價</th><th>數量</th><th>小計</th></tr>
            </thead>
            <tbody>
            @foreach($cart as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>${{ number_format($item['price'], 2) }}</td>
                    <td>{{ $item['qty'] }}</td>
                    <td>${{ number_format($item['price'] * $item['qty'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p>總計：<strong>${{ number_format($total, 2) }}</strong></p>

        <!-- 下單表單 -->
        <form action="{{ route('orders.store') }}" method="POST" style="margin-top:12px;">
            @csrf

            <div>
            <label for="address">配送地址</label><br>
            <textarea id="address" name="address" rows="2" cols="50">{{ old('address') }}</textarea>
            </div>

            <div>
            <label for="notes">備註（選填）</label><br>
            <textarea id="notes" name="notes" rows="2" cols="50">{{ old('notes') }}</textarea>
            </div>

            <div>
            <label for="payment_method">付款方式</label><br>
            <select id="payment_method" name="payment_method">
                <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>貨到付款</option>
            </select>
            </div>

            <div style="margin-top:8px;">
            <button type="submit">下單 (Checkout)</button>
            </div>
        </form>
        <!-- end 下單表單 -->

        <form action="{{ route('cart.clear') }}" method="POST" style="margin-top:8px;">
            @csrf
            <button type="submit" onclick="return confirm('清空購物車？')">清空購物車</button>
        </form>
    @endif

    <p><a href="{{ route('products.index') }}">回商品列表</a></p>
</body>
</html>