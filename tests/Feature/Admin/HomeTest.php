<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;
    // 未ログインのユーザーは管理者側のトップページにアクセスできない
    public function test_guest_cannot_access_admin_home()
    {
        $response = $this->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側のトップページにアクセスできない
    public function test_user_cannot_access_admin_home()
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側のトップページにアクセスできる
    public function test_admin_can_access_admin_home()
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.home'));
        $response->assertStatus(200);
    }
}
