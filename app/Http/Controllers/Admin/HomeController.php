<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 会員数取得
        $total_users = User::count();
        // 有料会員数
        $total_premium_users = DB::table('subscriptions')->where('stripe_status', 'active')->count();
        // 無料会員数
        $total_free_users = $total_users - $total_premium_users;
        // 店舗数
        $total_restaurants = Restaurant::count();
        // 予約数
        $total_reservations = Reservation::count();
        // 月間売り上げ
        $sales_for_this_month = 300 * $total_premium_users;
        
        return view('admin.home', compact('total_users', 'total_premium_users', 'total_free_users', 'total_restaurants', 'total_reservations', 'sales_for_this_month'));
    }
}