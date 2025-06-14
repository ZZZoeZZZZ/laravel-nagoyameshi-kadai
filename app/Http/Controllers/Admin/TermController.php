<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    /**
     * 利用規約ページ
     */
    public function index()
    {
        $term = Term::oldest()->first();

        return view('admin.terms.index', compact('term'));
    }

    /**
     * 利用規約編集ページ
     */
    public function edit(Term $term)
    {
        return view('admin.terms.edit', compact('term'));
    }

    /**
     * 利用規約更新機能
     */
    public function update(Request $request, Term $term)
    {
        // バリデーション
        $request->validate([
            'content' => 'required',
        ]);

        $term->content = $request->input('content');

        $term->save();

        return redirect()->route('admin.terms.index')->with('flash_message', '利用規約を編集しました。');
    }
}