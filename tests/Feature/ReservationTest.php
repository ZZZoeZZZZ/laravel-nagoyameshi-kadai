<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * 予約一覧ページ
     ******************************************/
    // 未ログインのユーザーは会員側の予約一覧ページにアクセスできない
    public function test_guest_cannot_access_reservations_index()
    {
        $response = $this->get(route('reservations.index'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側の予約一覧ページにアクセスできない
    public function test_free_user_cannot_access_reservations_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('reservations.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の予約一覧ページにアクセスできる
    public function test_premium_user_can_access_reservations_index()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->get(route('reservations.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の予約一覧ページにアクセスできない
    public function test_admin_cannot_access_reservations_index()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('reservations.index'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 予約ページ
     ******************************************/
    // 未ログインのユーザーは会員側の予約ページにアクセスできない
    public function test_guest_cannot_access_reservations_create()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('reservations.index', $restaurant));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側の予約ページにアクセスできない
    public function test_free_user_cannot_access_reservations_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('reservations.index', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の予約ページにアクセスできる
    public function test_premium_user_can_access_reservations_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($general_user)->get(route('reservations.index', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の予約ページにアクセスできない
    public function test_admin_cannot_access_reservations_create()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('reservations.index', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 予約機能
     ******************************************/
    // 未ログインのユーザーは予約できない
    public function test_guest_cannot_access_reservations_store()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation_data = [
            'reservation_date' => '2024-01-01',
            'reservation_time' => '00:00',
            'number_of_people' => 10
        ];

        $response = $this->post(route('restaurants.reservations.store', $restaurant), $reservation_data);
        $this->assertDatabaseMissing('reservations', ['reserved_datetime' => '2024-01-01 00:00', 'number_of_people' => 10]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は予約できない
    public function test_free_user_cannot_access_reservations_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation_data = [
            'reservation_date' => '2024-01-01',
            'reservation_time' => '00:00',
            'number_of_people' => 10
        ];

        $response = $this->actingAs($general_user)->post(route('restaurants.reservations.store', $restaurant), $reservation_data);
        $this->assertDatabaseMissing('reservations', ['reserved_datetime' => '2024-01-01 00:00', 'number_of_people' => 10]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は予約できる
    public function test_premium_user_can_access_reservations_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation_data = [
            'reservation_date' => '2024-01-01',
            'reservation_time' => '00:00',
            'number_of_people' => 10
        ];

        $response = $this->actingAs($general_user)->post(route('restaurants.reservations.store', $restaurant), $reservation_data);
        $this->assertDatabaseHas('reservations', ['reserved_datetime' => '2024-01-01 00:00', 'number_of_people' => 10]);
        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの管理者は予約できない
    public function test_admin_cannot_access_reservations_store()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation_data = [
            'reservation_date' => '2024-01-01',
            'reservation_time' => '00:00',
            'number_of_people' => 10
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reservations.store', $restaurant), $reservation_data);
        $this->assertDatabaseMissing('reservations', ['reserved_datetime' => '2024-01-01 00:00', 'number_of_people' => 10]);
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 予約キャンセル機能
     ******************************************/
    // 未ログインのユーザーは予約をキャンセルできない
    public function test_guest_cannot_access_reservations_destroy()
    {
        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は予約をキャンセルできない
    public function test_free_user_cannot_access_reservations_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人の予約をキャンセルできない
    public function test_premium_user_cannot_access_others_reservations_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの有料会員は自身の予約をキャンセルできる
    public function test_premium_user_can_access_own_reservations_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $general_user->id
        ]);

        $response = $this->actingAs($general_user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの管理者は予約をキャンセルできない
    public function test_admin_cannot_access_reservations_destroy()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ダミーデータ
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
        $response->assertRedirect(route('admin.home'));
    }
}