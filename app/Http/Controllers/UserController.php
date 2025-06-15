<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    /**
     * 会員情報ページ
     */
    public function index()
    {
        $user = Auth::user();

        return view('user.index', compact('user'));
    }

    /**
     * 会員情報編集ページ
     */
    public function edit(User $user)
    {
        $login_user_id = Auth::user()->id;

        if ($user->id !== $login_user_id) {
            // ログイン中のユーザーと一致しない場合：ログイン中のユーザーの会員情報ページにリダイレクト
            return redirect()->route(('user.index'))->with('error_message', '不正なアクセスです。');
        } else {
            // 正常な処理
            return view('user.edit', compact('user'));
        }
    }

    /**
     * 会員情報更新機能
     */
    public function update(Request $request, User $user)
    {
        $login_user_id = Auth::user()->id;

        if ($user->id !== $login_user_id) {
            // ログイン中のユーザーと一致しない場合：ログイン中のユーザーの会員情報ページにリダイレクト
            return redirect()->route(('user.index'))->with('error_message', '不正なアクセスです。');
        } else {
            // 正常な処理
            // バリデーション
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'kana' => ['required', 'string', 'regex:/\A[ァ-ヴー\s]+\z/u', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'postal_code' => ['required', 'digits:7'],
                'address' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'digits_between:10, 11'],
                'birthday' => ['nullable', 'digits:8'],
                'occupation' => ['nullable', 'string', 'max:255'],
            ]);

            $user->name = $request->input('name');
            $user->kana = $request->input('kana');
            $user->email = $request->input('email');
            $user->postal_code = $request->input('postal_code');
            $user->address = $request->input('address');
            $user->phone_number = $request->input('phone_number');
            $user->birthday = $request->input('birthday');
            $user->occupation = $request->input('occupation');

            $user->save();

            return redirect()->route('user.index')->with('flash_message', '会員情報を編集しました。');
        }
    }
}
