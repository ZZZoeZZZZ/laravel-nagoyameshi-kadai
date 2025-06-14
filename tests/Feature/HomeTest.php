<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;


class HomeTest extends TestCase
{
    // 未ログインのユーザーは会員側のトップページにアクセスできる
    public function test_guest_can_access_top_page(): void
    {
        // ダミーデータ
        Category::factory()->count(4)->create();
        Restaurant::factory()->count(8)->create();

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側のトップページにアクセスできる
    public function test_general_user_can_access_top_page(): void
    {
        // ダミーデータ
        Category::factory()->count(4)->create();
        Restaurant::factory()->count(8)->create();

        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get('/');
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のトップページにアクセスできない
    public function test_admin_user_cannot_access_top_page(): void
    {
        // ダミーデータ
        Category::factory()->count(4)->create();
        Restaurant::factory()->count(8)->create();

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/');
        $response->assertRedirect(route('admin.home'));
    }
}
