<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Term;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;
    // 未ログインのユーザーは会員側の会社概要ページにアクセスできる
    public function test_guest_can_access_term_index()
    {
        // ダミーデータ
        $term = Term::factory()->create();

        $response = $this->get(route('terms.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の会社概要ページにアクセスできる
    public function test_user_can_access_term_index()
    {
        // ダミーデータ
        $term = Term::factory()->create();

        // 一般ユーザー作成
        $general_user = User::factory()->create();

        $response = $this->actingAs($general_user)->get(route('terms.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の会社概要ページにアクセスできない
    public function test_admin_cannot_access_term_index()
    {
        // ダミーデータ
        $term = Term::factory()->create();

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('terms.index'));
        $response->assertRedirect(route('admin.home'));
    }
}
