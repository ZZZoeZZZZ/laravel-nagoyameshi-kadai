<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * 店舗一覧ページ
     ******************************************/
    // 未ログインのユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_guest_can_access_restaurants_index_page(): void
    {
        $response = $this->get(route('restaurants.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_general_user_can_access_restaurants_index_page(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の店舗一覧ページにアクセスできない
    public function test_admin_user_cannot_access_restaurants_index_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.index'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 店舗詳細ページ
     ******************************************/
    // 未ログインのユーザーは会員側の店舗詳細ページにアクセスできる
    public function test_guest_can_access_restaurants_show_page(): void
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        
        $response = $this->get(route('restaurants.show', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の店舗詳細ページにアクセスできる
    public function test_general_user_can_access_restaurants_show_page(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.show', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の店舗詳細ページにアクセスできない
    public function test_admin_user_cannot_access_restaurants_show_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.show', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }
}