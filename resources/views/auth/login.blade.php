<!DOCTYPE html>
<html>
<head>
    <title>登入</title>
</head>
<body>
    <h1>登入</h1>
    <form method="POST" action="{{ url('login') }}">
        @csrf
        <label>帳號：</label>
        <input type="text" name="username" value="{{ old('username') }}">
        @error('username')<div>{{ $message }}</div>@enderror
        <br>
        <label>密碼：</label>
        <input type="password" name="password">
        @error('password')<div>{{ $message }}</div>@enderror
        <br>
        <button type="submit">登入</button>
    </form>
    @if($errors->any())
        <div>{{ $errors->first('email') }}</div>
    @endif
    <a href="{{ url('register') }}">還沒有帳號？註冊</a>
</body>
</html>
