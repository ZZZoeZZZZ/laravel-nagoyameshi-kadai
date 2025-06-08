<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // 会員一覧ページ
    public function index(Request $request)
    {
        // 表示に使用する変数の初期化
        $users = array();
        $total = 0;

        // 検索キーワードを取得
        $keyword = $request->keyword;

        // キーワードの有無判断
        if ($keyword !== null) {
            // キーワードある場合は絞込
            $users = User::where('name', 'like', "%{$keyword}%")->orWhere('kana', 'like', "%" . $keyword . "%")
                ->paginate(15);
            $total_users = $users->total();
        } else {
            // キーワードない場合は全件
            $users = User::paginate(15);
            $total = $users->total();
        }

        return view('admin.users.index', compact('users', 'keyword', 'total'));
    }

    // 会員詳細ページ
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }
}
