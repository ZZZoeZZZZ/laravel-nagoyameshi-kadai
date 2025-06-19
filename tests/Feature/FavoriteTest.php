<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * お気に入り一覧ページ
     ******************************************/
    // 未ログインのユーザーは会員側のお気に入り一覧ページにアクセスできない
    public function test_guest_cannot_access_favorites_index()
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のお気に入り一覧ページにアクセスできない
    public function test_free_user_cannot_access_favorites_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('favorites.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側のお気に入り一覧ページにアクセスできる
    public function test_premium_user_can_access_favorites_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->get(route('favorites.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のお気に入り一覧ページにアクセスできない
    public function test_admin_cannot_access_favorites_index()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('favorites.index'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * お気に入り追加機能
     ******************************************/
    // 未ログインのユーザーはお気に入りに追加できない
    public function test_guest_cannot_access_favorites_store()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant->id));
        $this->assertDatabaseMissing('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお気に入りに追加できない
    public function test_free_user_cannot_access_favorites_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->post(route('favorites.store', $restaurant->id));
        $this->assertDatabaseMissing('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお気に入りに追加できる
    public function test_premium_user_can_access_favorites_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->post(route('favorites.store', $restaurant->id));
        $this->assertDatabaseHas('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertStatus(302);
    }

    // ログイン済みの管理者はお気に入りに追加できない
    public function test_admin_cannot_access_favorites_store()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('favorites.store', $restaurant->id));
        $this->assertDatabaseMissing('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * お気に入り解除機能
     ******************************************/
    // 未ログインのユーザーはお気に入りを解除できない
    public function test_guest_cannot_access_favorites_destroy()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->delete(route('favorites.destroy', $restaurant));
        $this->assertDatabaseHas('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお気に入りを解除できない
    public function test_free_user_cannot_access_favorites_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $general_user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->actingAs($general_user)->delete(route('favorites.destroy', $restaurant));
        $this->assertDatabaseHas('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお気に入りを解除できる
    public function test_premium_user_can_access_favorites_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $general_user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->actingAs($general_user)->delete(route('favorites.destroy', $restaurant));
        $this->assertDatabaseMissing('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertStatus(302);
    }

    // ログイン済みの管理者はお気に入りを解除できない
    public function test_admin_cannot_access_favorites_destroy()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->actingAs($admin, 'admin')->delete(route('favorites.destroy', $restaurant));
        $this->assertDatabaseHas('restaurant_user', ['restaurant_id' => $restaurant->id]);
        $response->assertRedirect(route('admin.home'));
    }
}
