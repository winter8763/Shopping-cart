<!DOCTYPE html>
<html>
<head>
    <title>個人資料</title>
</head>
<body>
    <h1>個人資料</h1>
    @if(session('success'))
        <div>{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ url('profile') }}">
        @csrf
        <label>姓名：</label>
        <input type="text" name="name" value="{{ $user->name }}">
        @error('name')<div>{{ $message }}</div>@enderror
        <br>
        <label>Email：</label>
        <input type="email" name="email" value="{{ $user->email }}">
        @error('email')<div>{{ $message }}</div>@enderror
        <br>
        <label>電話：</label>
        <input type="text" name="phone" value="{{ $user->phone }}">
        @error('phone')<div>{{ $message }}</div>@enderror
        <br>
        <label>地址：</label>
        <input type="text" name="address" value="{{ $user->address }}">
        @error('address')<div>{{ $message }}</div>@enderror
        <br>
        <label>新密碼：</label>
        <input type="password" name="password">
        @error('password')<div>{{ $message }}</div>@enderror
        <br>
        <label>確認新密碼：</label>
        <input type="password" name="password_confirmation">
        <br>
        <button type="submit">更新資料</button>
    </form>
    <form method="POST" action="{{ url('logout') }}">
        @csrf
        <button type="submit">登出</button>
    </form>
</body>
</html>
