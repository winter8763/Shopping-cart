<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validate([
            'username' => 'required|string|max:50|alpha_num|unique:users,username',
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        // 逐欄賦值，直觀
        $user = new User();
        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->phone = $data['phone'] ?? null;
        $user->address = $data['address'] ?? null;
        $user->role = 'member'; // 強制只能為 member


        DB::transaction(function () use ($user) {
            $user->save();
            // 寄發驗證信
            $user->sendEmailVerificationNotification();
        });

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
        // 驗證輸入
        $data = $request->validate([
            'username' => 'required|string|max:50|alpha_num',
            'password' => 'required|string',
        ]);

        // 寫入憑證
        $credential = [
            'username' => $data['username'],
            'password' => $data['password'],
        ];

        // 驗證使用者身份
        if (auth()->attempt($credential)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // 若尚未驗證電子郵件，登出並回傳錯誤（可同時重新寄驗證信）
            if (! $user->hasVerifiedEmail()) {
                // 重新寄送驗證信
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
        // 清除 session 使之失效 並重新產生 CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // 顯示使用者個人資料
    public function profile()
    {
        $user = auth()->user();

        // 精準寫法
        $user = User::select('name', 'email', 'phone', 'address')
            ->where('id', $user->id)
            ->first();

        // $result = [];
        // $result['name'] = $user->name;
        // $result['email'] = $user->email;
        // $result['phone'] = $user->phone;
        // $result['address'] = $user->address;

        return view('profile', ['user' => $user]);
    }

    // 更新使用者個人資料
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:50|alpha_num|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
