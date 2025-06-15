<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /******************************************
     * 会員情報ページ
     ******************************************/
    // 未ログインのユーザーは会員側の会員情報ページにアクセスできない
    public function test_guest_cannot_access_user_index_page(): void
    {
        $this->get(route('user.index'));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは会員側の会員情報ページにアクセスできる
    public function test_general_user_can_access_user_index_page(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('user.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の会員情報ページにアクセスできない
    public function test_admin_user_cannot_access_user_index_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('user.index'));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 会員情報編集ページ
     ******************************************/
    // 未ログインのユーザーは会員側の会員情報編集ページにアクセスできない
    public function test_guest_cannot_access_user_edit_page(): void
    {
        // テスト用ダミーデータ
        $user = User::factory()->create();

        $this->get(route('user.edit', $user));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは会員側の他人の会員情報編集ページにアクセスできない
    public function test_general_user_cannot_access_other_user_edit_page(): void
    {
        // テスト用ダミーデータ
        $user = User::factory()->create();

        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('user.edit', $user));
        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの一般ユーザーは会員側の自身の会員情報編集ページにアクセスできる
    public function test_general_user_cannot_access_own_edit_page(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('user.edit', $general_user));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の会員情報編集ページにアクセスできない
    public function test_admin_user_cannot_access_general_user_edit_page(): void
    {
        // テスト用ダミーデータ
        $user = User::factory()->create();

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('user.edit', $user));
        $response->assertRedirect(route('admin.home'));
    }

    /******************************************
     * 会員情報更新機能
     ******************************************/
    // 未ログインのユーザーは会員情報を更新できない
    public function test_guest_cannot_update_general_user(): void
    {
        // テスト用ダミーデータ
        $user = User::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        // 更新
        $response = $this->patch(route('user.update', $user), $update_data);

        $this->assertDatabaseMissing('users', $update_data);
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは他人の会員情報を更新できない
    public function test_general_user_cannot_update_other_user(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // テスト用ダミーデータ
        $user = User::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        // 更新
        $response = $this->actingAs($general_user)->patch(route('user.update', $user), $update_data);

        $this->assertDatabaseMissing('users', $update_data);
        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの一般ユーザーは自身の会員情報を更新できる
    public function test_general_user_can_update_own_user(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // テスト用ダミーデータ
        $update_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        // 更新
        $response = $this->actingAs($general_user)->patch(route('user.update', $general_user), $update_data);

        $this->assertDatabaseHas('users', $update_data);
        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの管理者は会員情報を更新できない
    public function test_admin_user_cannot_update_general_user(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $user = User::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        // 更新
        $response = $this->actingAs($admin, 'admin')->patch(route('user.update', $user), $update_data);

        $this->assertDatabaseMissing('users', $update_data);
        $response->assertRedirect(route('admin.home'));
    }
}
