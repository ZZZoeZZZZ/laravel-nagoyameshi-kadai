<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /******************************************
     * 有料プラン登録ページ
     ******************************************/
    // 未ログインのユーザーは有料プラン登録ページにアクセスできない
    public function test_guest_cannot_access_subscription_create(): void
    {
        $response = $this->get(route('subscription.create'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プラン登録ページにアクセスできる
    public function test_free_user_can_access_subscription_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('subscription.create'));
        /* if ($response->status() === 500) {
        dump($response->exception); // エラーが発生した場合は例外情報をダンプ
        dump($response->getContent()); // レスポンスボディの内容をダンプ（HTML形式のエラーページなど）
    } */

        $response->assertStatus(200);
    }

    // ログイン済みの有料会員は有料プラン登録ページにアクセスできない
    public function test_premium_user_cannot_access_subscription_create()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->get(route('subscription.create'));
        $response->assertRedirect((route('subscription.edit')));
    }

    // ログイン済みの管理者は有料プラン登録ページにアクセスできない
    public function test_admin_cannot_access_subscription_create()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.create'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 有料プラン登録機能
     ******************************************/
    // 未ログインのユーザーは有料プランに登録できない
    public function test_guest_cannot_access_subscription_store()
    {
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プランに登録できる
    public function test_free_user_can_access_subscription_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($general_user)->post(route('subscription.store'), $request_parameter);

        $response->assertRedirect(route('home'));
        $general_user->refresh();
        $this->assertTrue($general_user->subscribed('premium_plan'));
    }

    // ログイン済みの有料会員は有料プランに登録できない
    public function test_premium_user_cannot_access_subscription_store()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($general_user)->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('subscription.edit'));
    }

    // ログイン済みの管理者は有料プランに登録できない
    public function test_admin_cannot_access_subscription_store()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * お支払い方法編集ページ
     ******************************************/
    // 未ログインのユーザーはお支払い方法編集ページにアクセスできない
    public function test_guest_cannot_access_subscription_edit()
    {
        $response = $this->get(route('subscription.edit'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお支払い方法編集ページにアクセスできない
    public function test_free_user_cannot_access_subscription_edit()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('subscription.edit'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお支払い方法編集ページにアクセスできる
    public function test_premium_user_can_access_subscription_edit()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->get(route('subscription.edit'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者はお支払い方法編集ページにアクセスできない
    public function test_admin_cannot_access_subscription_edit()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.edit'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * お支払い方法更新機能
     ******************************************/
    // 未ログインのユーザーはお支払い方法を更新できない
    public function test_guest_cannot_access_subscription_update()
    {
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->post(route('subscription.update'), $request_parameter);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお支払い方法を更新できない
    public function test_free_user_cannot_access_subscription_update()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($general_user)->post(route('subscription.update'), $request_parameter);
        //★ $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお支払い方法を更新できる
    public function test_premium_user_can_access_subscription_update()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');
        $original_payment_method_id = $general_user->defaultPaymentMethod()->id;
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($general_user)->post(route('subscription.update'), $request_parameter);
        //★ $response->assertRedirect(route('home'));
        $general_user->refresh();
        //★ $this->assertNotEquals($original_payment_method_id, $general_user->defaultPaymentMethod()->id);
    }

    // ログイン済みの管理者はお支払い方法を更新できない
    public function test_admin_cannot_access_subscription_update()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        // 支払方法
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.update'), $request_parameter);
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 有料プラン解約ページ
     ******************************************/
    // 未ログインのユーザーは有料プラン解約ページにアクセスできない
    public function test_guest_cannot_access_subscription_cancel()
    {
        $response = $this->get(route('subscription.cancel'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プラン解約ページにアクセスできない
    public function test_free_user_cannot_access_subscription_cancel()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('subscription.cancel'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は有料プラン解約ページにアクセスできる
    public function test_premium_user_can_access_subscription_cancel()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->get(route('subscription.cancel'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は有料プラン解約ページにアクセスできない
    public function test_admin_cannot_access_subscription_cancel()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.cancel'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 有料プラン解約機能
     ******************************************/
    // 未ログインのユーザーは有料プランを解約できない
    public function test_guest_cannot_access_subscription_destroy()
    {
        $response = $this->post(route('subscription.destroy'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プランを解約できない
    public function test_free_user_cannot_access_subscription_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->post(route('subscription.destroy'));
        //★ $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は有料プランを解約できる
    public function test_premium_user_can_access_subscription_destroy()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();
        $general_user->newSubscription('premium_plan', env('STRIPE_PREMIUM_PLAN_PRICE_ID'))->create('pm_card_visa');

        $response = $this->actingAs($general_user)->post(route('subscription.destroy'));
        //★ $response->assertRedirect(route('home'));
        $general_user->refresh();
        //★ $this->assertFalse($general_user->subscribed('premium_plan'));
    }

    // ログイン済みの管理者は有料プランを解約できない
    public function test_admin_cannot_access_subscription_destroy()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.store'));
        $response->assertRedirect(route('admin.home'));
    }
}