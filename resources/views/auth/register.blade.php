<!DOCTYPE html>
<html>
<head>
    <title>註冊</title>
</head>
<body>
    <h1>註冊</h1>
    <form method="POST" action="{{ url('register') }}">
        @csrf
        <label>姓名：</label>
        <input type="text" name="name" value="{{ old('name') }}">
        @error('name')<div>{{ $message }}</div>@enderror
        <br>
        <label>帳號：</label>
        <input type="text" name="username" value="{{ old('username') }}">
        @error('username')<div>{{ $message }}</div>@enderror
        <br>
        <label>Email：</label>
        <input type="email" name="email" value="{{ old('email') }}">
        @error('email')<div>{{ $message }}</div>@enderror
        <br>
        <label>密碼：</label>
        <input type="password" name="password">
        @error('password')<div>{{ $message }}</div>@enderror
        <br>
        <label>確認密碼：</label>
        <input type="password" name="password_confirmation">
        <br>
        <button type="submit">註冊</button>
    </form>
    <a href="{{ url('login') }}">已有帳號？登入</a>
</body>
</html>
