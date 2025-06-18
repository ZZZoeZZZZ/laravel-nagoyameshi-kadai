<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * レビュー一覧ページ
     */
    public function index(Restaurant $restaurant)
    {
        if (Auth::user()->subscribed('premium_plan')) {
            // 有料プラン登録の場合は全件
            $reviews = Review::where('restaurant_id', $restaurant->id)->orderBy('created_at', 'desc')->paginate(5);
        } else {
            // 他は3件まで
            $reviews = Review::where('restaurant_id', $restaurant->id)->orderBy('created_at', 'desc')->take(3)->get();
        }

        return view('reviews.index', compact('restaurant', 'reviews'));
    }

    /**
     * レビュー投稿ページ
     */
    public function create(Restaurant $restaurant)
    {
        return view('reviews.create', compact('restaurant'));
    }

    /**
     * レビュー投稿機能
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        // バリデーション
        $request->validate([
            'score' => 'required|numeric|between:1,5',
            'content' => 'required'
        ]);

        // レビュー作成
        $review = new Review();
        $review->score = $request->input('score');
        $review->content = $request->input('content');
        $review->restaurant_id  = $restaurant->id;
        $review->user_id = $request->user()->id;
        $review->save();

        return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集ページ
     */
    public function edit(Restaurant $restaurant, Review $review)
    {
        // 他人のレビューページにはアクセスできない
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        } else {
            return view('reviews.edit', compact('restaurant', 'review'));
        }
    }

    /**
     * レビュー更新機能
     */
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        // 他人のレビューは更新できない
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        } else {
            // バリデーション
            $request->validate([
                'score' => 'required|numeric|between:1,5',
                'content' => 'required',
            ]);

            $review->score = $request->input('score');
            $review->content = $request->input('content');
            $review->save();

            return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを編集しました。');
        }
    }

    /**
     * レビュー削除機能
     */
    public function destroy(Restaurant $restaurant, Review $review)
    {
        // 他人のレビューは削除できない
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        } else {
            $review->delete();
            return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを削除しました。');
        }
    }
}
