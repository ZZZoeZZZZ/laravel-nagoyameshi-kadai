<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /******************************************
     * レビュー一覧ページ
     ******************************************/
    // 未ログインのユーザーは会員側のレビュー一覧ページにアクセスできない
    public function test_guest_cannot_access_reviews_index()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_free_user_can_access_reviews_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの有料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_premium_user_can_access_reviews_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー一覧ページにアクセスできない
    public function test_admin_cannot_access_reviews_index()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * レビュー投稿ページ
     ******************************************/
    // 未ログインのユーザーは会員側のレビュー投稿ページにアクセスできない
    public function test_guest_cannot_access_reviews_create()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のレビュー投稿ページにアクセスできない
    public function test_free_user_cannot_access_reviews_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側のレビュー投稿ページにアクセスできる
    public function test_premium_user_can_access_reviews_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.create', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー投稿ページにアクセスできない
    public function test_admin_cannot_access_reviews_create()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * レビュー投稿機能
     ******************************************/
    // 未ログインのユーザーはレビューを投稿できない
    public function test_guest_cannot_access_reviews_store()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review_data = [
            'score' => 1,
            'content' => 'テスト'
        ];

        $response = $this->post(route('restaurants.reviews.store', $restaurant), $review_data);

        $this->assertDatabaseMissing('reviews', $review_data);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを投稿できない
    public function test_free_user_cannot_access_reviews_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review_data = [
            'score' => 1,
            'content' => 'テスト'
        ];

        $response = $this->actingAs($general_user)->post(route('restaurants.reviews.store', $restaurant), $review_data);

        $this->assertDatabaseMissing('reviews', $review_data);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はレビューを投稿できる
    public function test_premium_user_can_access_reviews_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review_data = [
            'score' => 1,
            'content' => 'テスト'
        ];

        $response = $this->actingAs($general_user)->post(route('restaurants.reviews.store', $restaurant), $review_data);

        // ★ $this->assertDatabaseHas('reviews', $review_data);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを投稿できない
    public function test_admin_cannot_access_reviews_store()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review_data = [
            'score' => 1,
            'content' => 'テスト'
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reviews.store', $restaurant), $review_data);

        $this->assertDatabaseMissing('reviews', $review_data);
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * レビュー編集ページ
     ******************************************/
    // 未ログインのユーザーは会員側のレビュー編集ページにアクセスできない
    public function test_guest_cannot_access_reviews_edit()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のレビュー編集ページにアクセスできない
    public function test_free_user_cannot_access_reviews_edit()
    {

        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の他人のレビュー編集ページにアクセスできない
    public function test_premium_user_cannot_access_others_reviews_edit()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は会員側の自身のレビュー編集ページにアクセスできる
    public function test_premium_user_can_access_own_reviews_edit()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー編集ページにアクセスできない
    public function test_admin_cannot_access_reviews_edit()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('login'));
    }

    /******************************************
     * レビュー更新機能
     ******************************************/
    // 未ログインのユーザーはレビューを更新できない
    public function test_guest_cannot_access_reviews_update()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);
        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]), $new_review_data);

        $this->assertDatabaseMissing('reviews', $new_review_data);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを更新できない
    public function test_free_user_cannot_access_reviews_update()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);
        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->actingAs($general_user)->patch(route('restaurants.reviews.update', [$restaurant, $review]), $new_review_data);

        $this->assertDatabaseMissing('reviews', $new_review_data);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人のレビューを更新できない
    public function test_premium_user_cannot_access_others_reviews_update()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);
        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->actingAs($general_user)->patch(route('restaurants.reviews.update', [$restaurant, $review]), $new_review_data);

        $this->assertDatabaseMissing('reviews', $new_review_data);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は自身のレビューを更新できる
    public function test_premium_user_can_access_own_reviews_update()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);
        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->actingAs($general_user)->patch(route('restaurants.reviews.update', [$restaurant, $review]), $new_review_data);

        $this->assertDatabaseHas('reviews', $new_review_data);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを更新できない
    public function test_admin_cannot_access_reviews_update()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);
        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('restaurants.reviews.update', [$restaurant, $review]), $new_review_data);

        $this->assertDatabaseMissing('reviews', $new_review_data);
        $response->assertRedirect(route('admin.home'));
    }
    /******************************************
     * レビュー削除機能
     ******************************************/
    // 未ログインのユーザーはレビューを削除できない
    public function test_guest_cannot_access_reviews_destroy()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを削除できない
    public function test_free_user_cannot_access_reviews_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人のレビューを削除できない
    public function test_premium_user_cannot_access_others_reviews_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は自身のレビューを削除できる
    public function test_premium_user_can_access_own_reviews_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを削除できない
    public function test_admin_cannot_access_reviews_destroy()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        // ★$response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }
}