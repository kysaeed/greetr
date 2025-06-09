<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $isLoginSuccess = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], true);

        if ($isLoginSuccess) {
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'ログインに成功しました',
                'user' => Auth::user()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'メールアドレスまたはパスワードが正しくありません'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'ログアウトしました'
        ]);
    }

    /**
     * ログイン後のリダイレクト先
     */
    protected function redirectTo(): string
    {
        return '/';
    }
}