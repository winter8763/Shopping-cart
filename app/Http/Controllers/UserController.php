<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    // 顯示註冊表單
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // 處理註冊請求
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->input('phone', null),
            'address' => $request->input('address', null),
            'role' => 'member',
        ]);

        // 寄發驗證信
        $user->sendEmailVerificationNotification();

        // 登出（若想自動登入可移除 logout）
        auth()->logout();

        // 不自動登入：導向提示頁告知使用者檢查信箱
        return redirect()->route('verification.notice');
    }

    // 顯示登入表單
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 處理登入請求
    public function login(Request $request)
    {
        $credential = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (auth()->attempt($credential)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // 若尚未驗證電子郵件，登出並回傳錯誤（可同時重新寄驗證信）
            if (! $user->hasVerifiedEmail()) {
                // 可選：重新寄送驗證信
                $user->sendEmailVerificationNotification();

                auth()->logout();
                return back()->withErrors([
                    'email' => '請先完成電子郵件驗證。我們已重新寄出驗證信，請檢查您的信箱。',
                ]);
            }

            return redirect()->intended('profile');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // 處理登出請求
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // 顯示使用者個人資料
    public function profile()
    {
        return view('profile', ['user' => auth()->user()]);
    }

    // 更新使用者個人資料
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
