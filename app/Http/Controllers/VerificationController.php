<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // 重新寄送驗證信（需登入）
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', '驗證信已重新寄出。');
    }

    // 顯示「請驗證信箱」頁面
    public function notice()
    {
        return view('auth.verify_notice');
    }

    // 驗證連結處理
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // 驗證 hash 是否與該使用者 email 對應（安全檢查）
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // 驗證成功後導回登入頁（也可改為導到會員頁面）
        return redirect()->route('login', ['verified' => 1]);
    }
}
