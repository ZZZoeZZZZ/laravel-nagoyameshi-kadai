<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\RegularHoliday;
use Illuminate\Http\Request;
use App\Http\Requests\RestaurantStoreRequest;

class RestaurantController extends Controller
{
    /**
     * 店舗一覧ページ
     */
    public function index(Request $request)
    {
        // 変数初期化
        $restaurants = array();
        $keyword = null;
        $total = 0;

        // 検索キーワードを取得
        $keyword = $request->keyword;

        if ($keyword !== null) {
            // キーワードある場合は絞り込み
            $restaurants = Restaurant::where('name', 'like', '%' . $keyword . '%')->paginate(15);
            $total = $restaurants->total();
        } else {
            // キーワードない場合は全件
            $restaurants = Restaurant::paginate(15);
            $total = $restaurants->total();
        }

        return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
    }

    /**
     * 店舗登録ページ
     */
    public function create()
    {
        // カテゴリを取得
        $categories = Category::all();
        // 定休日を取得
        $regular_holidays = RegularHoliday::all();

        return view('admin.restaurants.create', compact('categories', 'regular_holidays'));
    }

    /**
     * 店舗登録機能
     * フォームリクエストでバリデーションを行う
     */
    public function store(RestaurantStoreRequest $request)
    {
        $restaurant = new Restaurant();

        if ($request->image !== null) {
            // 画像がある場合：画像を保存し、ファイル名をDBに保存する
            $file_path = $request->file('image')->store('public/restaurants');
            $restaurant->image = basename($file_path);
        } else {
            // 画像がない場合
            $restaurant->image = '';
        }

        // 入力内容をDBに保存する
        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');

        $restaurant->save();

        // 店舗_カテゴリの中間テーブルに保存する
        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);
        // 店舗_定休日の中間テーブルに保存する
        $regular_holiday_ids = array_filter($request->input('regular_holiday_ids'));
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を登録しました。');
    }

    /**
     * 店舗詳細ページ
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    /**
     * 店舗編集ページ
     */
    public function edit(Restaurant $restaurant)
    {
        // カテゴリを取得
        $categories = Category::all();
        // 定休日を取得
        $regular_holidays = RegularHoliday::all();

        // 編集する店舗に設定済みのカテゴリを取得
        $category_ids = $restaurant->categories->pluck('id')->toArray();

        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids', 'regular_holidays'));
    }

    /**
     * 店舗更新機能
     */
    public function update(RestaurantStoreRequest $request, Restaurant $restaurant)
    {
        if ($request->image !== null) {
            // 画像がある場合：画像を保存し、ファイル名をDBに保存する
            $file_path = $request->file('image')->store('public/restaurants');
            $restaurant->image = basename($file_path);
        } else {
            // 画像がない場合
            $restaurant->image = '';
        }
        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');

        $restaurant->save();

        // 店舗_カテゴリの中間テーブルに保存する
        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        // 店舗_定休日の中間テーブルに保存する
        $regular_holiday_ids = array_filter(($request->input('regular_holiday_ids')));
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        return redirect()->route('admin.restaurants.show', $restaurant)->with('flash_message', '店舗を編集しました。');
    }

    /**
     * 店舗削除機能
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を削除しました。');
    }
}