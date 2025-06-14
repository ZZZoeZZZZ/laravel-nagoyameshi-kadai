<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Admin;
use App\Models\Category;
use App\Models\RegularHoliday;
use Illuminate\Support\Facades\Hash;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * 店舗一覧ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の店舗一覧ページにアクセスできない
    public function test_guest_cannot_access_restaurant_index_page(): void
    {
        $this->get(route('admin.restaurants.index'));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の店舗一覧ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_index_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.restaurants.index'));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の店舗一覧ページにアクセスできる
    public function test_admin_user_can_access_restaurant_index_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.index'));
        $response->assertStatus(200);
    }

    /******************************************
     * 店舗詳細ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の店舗詳細ページにアクセスできない
    public function test_guest_cannot_access_restaurant_show_page(): void
    {
        $this->get('/admin/restaurants/1');
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の店舗詳細ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_show_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get('/admin/restaurants/1');
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の店舗詳細ページにアクセスできる
    public function test_admin_user_can_access_restaurant_show_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.show', $restaurant));
        $response->assertStatus(200);
    }

    /******************************************
     * 店舗登録ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の店舗登録ページにアクセスできない
    public function test_guest_cannot_access_restaurant_create_page(): void
    {
        $this->get('/admin/restaurants/create');
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の店舗登録ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_create_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get('/admin/restaurants/create');
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の店舗登録ページにアクセスできる
    public function test_admin_user_can_access_restaurant_create_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/restaurants/create');
        $response->assertStatus(200);
    }


    /******************************************
     * 店舗登録機能
     ******************************************/
    // 未ログインのユーザーは店舗を登録できない
    public function test_guest_cannot_store_restaurant()
    {
        // 登録用データ
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $restaurant = [
            'name' => 'テスト',
            'description' => 'テスト',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '0000000',
            'address' => 'テスト',
            'opening_time' => '10:00:00',
            'closing_time' => '20:00:00',
            'seating_capacity' => 50,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];


        // 登録
        $response = $this->post(route('admin.restaurants.store'), $restaurant);

        // 検証
        // Restaurantsテーブルにカテゴリ・定休日列はないため削除
        unset($restaurant['category_ids'], $restaurant['regular_holiday_ids']);
        $this->assertDatabaseMissing('restaurants', $restaurant);
        // 店舗の登録ができていないため、restaurant_idは使用しない
        foreach ($category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseMissing('regular_holiday_restaurant', [
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは店舗を登録できない
    public function test_general_user_cannot_store_restaurant()
    {
        // 登録用データ
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $restaurant = [
            'name' => 'テスト',
            'description' => 'テスト',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '0000000',
            'address' => 'テスト',
            'opening_time' => '10:00:00',
            'closing_time' => '20:00:00',
            'seating_capacity' => 50,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];

        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 登録
        $response = $this->actingAs($generalUser)->post(route('admin.restaurants.store'), $restaurant);

        unset($restaurant['category_ids'], $restaurant['regular_holiday_ids']);
        $this->assertDatabaseMissing('restaurants', $restaurant);
        // 店舗の登録ができていないため、restaurant_idは使用しない
        foreach ($category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseMissing('regular_holiday_restaurant', [
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は店舗を登録できる
    public function test_admin_user_can_store_restaurant()
    {
        // 登録用データ
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $restaurant = [
            'name' => 'テスト',
            'description' => 'テスト',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '0000000',
            'address' => 'テスト',
            'opening_time' => '10:00:00',
            'closing_time' => '20:00:00',
            'seating_capacity' => 50,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 登録
        $response = $this->actingAs($admin, 'admin')->post(route('admin.restaurants.store'), $restaurant);

        unset($restaurant['category_ids'], $restaurant['regular_holiday_ids']);
        $this->assertDatabaseHas('restaurants', $restaurant);
        $restaurant = Restaurant::latest('id')->first();

        foreach ($category_ids as $category_id) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => $restaurant->id,
                'category_id' => $category_id,
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseHas('regular_holiday_restaurant', [
                'restaurant_id' => $restaurant->id,
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.restaurants.index'));
    }

    /******************************************
     * 店舗編集ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の店舗編集ページにアクセスできない
    public function test_guest_cannot_access_restaurant_edit_page(): void
    {
        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $this->get(route('admin.restaurants.edit', ['restaurant' =>  $restaurant->id]));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の店舗編集ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_edit_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.restaurants.edit', ['restaurant' =>  $restaurant->id]));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の店舗編集ページにアクセスできる
    public function test_admin_user_can_access_restaurant_edit_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.edit', ['restaurant' => $restaurant->id]));
        $response->assertStatus(200);
    }

    /******************************************
     * 店舗更新機能
     ******************************************/
    // 未ログインのユーザーは店舗を更新できない
    public function test_guest_cannot_update_restaurant(): void
    {
        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $updateData = [
            'name' => 'テスト更新',
            'description' => 'テスト更新',
            'lowest_price' => 5000,
            'highest_price' => 10000,
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'opening_time' => '13:00:00',
            'closing_time' => '23:00:00',
            'seating_capacity' => 100,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];

        // 更新
        $response = $this->patch(route('admin.restaurants.update', $restaurant), $updateData);

        unset($updateData['category_ids'], $updateData['regular_holiday_ids']);
        $this->assertDatabaseMissing('restaurants', $updateData);
        $updateData = Restaurant::latest('id')->first();
        foreach ($category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'restaurant_id' => $updateData->id,
                'category_id' => $category_id
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseMissing('regular_holiday_restaurant', [
                'restaurant_id' => $updateData->id,
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは店舗を更新できない
    public function test_general_user_cannot_update_restaurant(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $updateData = [
            'name' => 'テスト更新',
            'description' => 'テスト更新',
            'lowest_price' => 5000,
            'highest_price' => 10000,
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'opening_time' => '13:00:00',
            'closing_time' => '23:00:00',
            'seating_capacity' => 100,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];

        // 更新
        $response = $this->actingAs($generalUser)->patch(route('admin.restaurants.update', $restaurant), $updateData);

        unset($updateData['category_ids'], $updateData['regular_holiday_ids']);
        $this->assertDatabaseMissing('restaurants', $updateData);
        $updateData = Restaurant::latest('id')->first();
        foreach ($category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'restaurant_id' => $updateData->id,
                'category_id' => $category_id
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseMissing('regular_holiday_restaurant', [
                'restaurant_id' => $updateData->id,
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は店舗を更新できる
    public function test_admin_user_can_update_restaurant(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $category_ids = $categories->pluck('id')->toArray();
        $regular_holidays = RegularHoliday::factory()->create();
        $regular_holiday_ids = $regular_holidays->pluck('id')->toArray();
        $updateData = [
            'name' => 'テスト更新',
            'description' => 'テスト更新',
            'lowest_price' => 5000,
            'highest_price' => 10000,
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'opening_time' => '13:00:00',
            'closing_time' => '23:00:00',
            'seating_capacity' => 100,
            'category_ids' => $category_ids,
            'regular_holiday_ids' => $regular_holiday_ids,
        ];

        // 更新
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.restaurants.update', $restaurant), $updateData);

        unset($updateData['category_ids'], $updateData['regular_holiday_ids']);
        $this->assertDatabaseHas('restaurants', $updateData);
        $updateData = Restaurant::latest('id')->first();
        foreach ($category_ids as $category_id) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => $updateData->id,
                'category_id' => $category_id
            ]);
        }
        foreach ($regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseHas('regular_holiday_restaurant', [
                'restaurant_id' => $updateData->id,
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
        $response->assertRedirect(route('admin.restaurants.show', $restaurant));
    }

    /******************************************
     * 店舗削除機能
     ******************************************/
    // 未ログインのユーザーは店舗を削除できない
    public function test_guest_cannot_destroy_restaurant(): void
    {
        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 削除
        $response = $this->delete(route('admin.restaurants.destroy', $restaurant));
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは店舗を削除できない
    public function test_general_user_cannot_destroy_restaurant(): void
    {

        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 削除
        $response = $this->actingAs($generalUser)->delete(route('admin.restaurants.destroy', $restaurant));
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は店舗を削除できる
    public function admin_user_cannot_destroy_restaurant(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $restaurant = Restaurant::factory()->create();

        // 削除
        $response = $this->actingAs($admin, 'admin')->delete(route('admin.restaurants.destroy', $restaurant));
        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
        $response->assertRedirect(route('admin.restaurants.index'));
    }
}