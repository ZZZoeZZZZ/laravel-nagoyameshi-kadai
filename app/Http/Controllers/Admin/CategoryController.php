<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * カテゴリ一覧ページ
     */
    public function index(Request $request)
    {
        // 変数初期化
        $categories = array();
        $keyword = null;
        $total = 0;

        $keyword = $request->keyword;

        if ($keyword !== null) {
            $categories = Category::where('name', 'like', '%' . $keyword . '%')->pagenate(15);
        } else {
            $categories = Category::paginate(15);
        }
        $total = $categories->total();

        return view('admin.categories.index', compact('categories', 'keyword', 'total'));
    }

    /**
     * カテゴリ登録機能
     */
    public function store(CategoryRequest $request)
    {
        $category = new Category();
        $category->name = $request->input('name');
        $category->save();

        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを登録しました。');
    }

    /**
     * カテゴリ更新機能
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->name = $request->input('name');
        $category->save();

        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを編集しました。');
    }

    /**
     * カテゴリ削除機能
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを削除しました。');
    }
}
