<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /******************************************
     * 会員一覧ページ
     ******************************************/

    // 未ログインのユーザーは管理者側の会員一覧ページにアクセスできない
    public function test_guest_cannot_access_user_index_page(): void
    {
        $this->get('/admin/users');
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の会員一覧ページにアクセスできない
    public function test_general_user_cannnot_access_user_index_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get('/admin/users');
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の会員一覧ページにアクセスできる
    public function test_admin_user_can_access_user_index_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/users');
        $response->assertStatus(200);
    }


    /******************************************
     * 会員詳細ページ
     ******************************************/

    // 未ログインのユーザーは管理者側の会員詳細ページにアクセスできない
    public function test_guest_cannot_access_user_show_page(): void
    {
        $this->get('/admin/users/1');
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の会員詳細ページにアクセスできない
    public function test_general_user_cannnot_access_user_show_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get('/admin/users/1');
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の会員詳細ページにアクセスできる
    public function test_admin_user_can_access_user_show_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/users');
        $response->assertStatus(200);
    }
}
