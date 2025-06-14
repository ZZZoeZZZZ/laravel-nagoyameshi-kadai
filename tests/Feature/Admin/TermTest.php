<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Term;
use Illuminate\Support\Facades\Hash;

class TermTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * 利用規約ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の利用規約ページにアクセスできない
    public function test_guest_cannot_access_terms_index_page(): void
    {
        // テスト用ダミーデータ
        Term::factory()->create();

        $this->get(route('admin.terms.index'));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の利用規約ページにアクセスできない
    public function test_general_user_cannnot_access_company_index_page(): void
    {
        // テスト用ダミーデータ
        Term::factory()->create();

        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.terms.index'));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の利用規約ページにアクセスできる
    public function test_admin_user_can_access_companyt_index_page(): void
    {
        // テスト用ダミーデータ
        Term::factory()->create();

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.terms.index'));
        $response->assertStatus(200);
    }

    /******************************************
     * 利用規約編集ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の利用規約編集ページにアクセスできない
    public function test_guest_cannot_access_restaurant_edit_page(): void
    {
        // テスト用ダミーデータ
        $term = Term::factory()->create();

        $this->get(route('admin.terms.edit', ['term' =>  $term->id]));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の利用規約編集ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_edit_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $term = Term::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.terms.edit', ['term' =>  $term->id]));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の利用規約編集ページにアクセスできる
    public function test_admin_user_can_access_restaurant_edit_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $term = Term::factory()->create();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.terms.edit', ['term' => $term->id]));
        $response->assertStatus(200);
    }

    /******************************************
     * 利用規約更新機能
     ******************************************/
    // 未ログインのユーザーは利用規約を更新できない
    public function test_guest_cannot_update_company(): void
    {
        // テスト用ダミーデータ
        $term = Term::factory()->create();
        $update_data = [
            'content' => 'テスト更新',
        ];

        // 更新
        $response = $this->patch(route('admin.terms.update', $term), $update_data);

        $this->assertDatabaseMissing('terms', $update_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは利用規約を更新できない
    public function test_general_user_cannot_update_company(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // テスト用ダミーデータ
        $term = Term::factory()->create();
        $update_data = [
            'content' => 'テスト更新',
        ];

        // 更新
        $response = $this->actingAs($general_user)->patch(route('admin.terms.update', $term), $update_data);

        $this->assertDatabaseMissing('terms', $update_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は利用規約を更新できる
    public function test_admin_user_can_update_company(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $term = Term::factory()->create();
        $update_data = [
            'content' => 'テスト更新',
        ];

        // 更新
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.terms.update', $term), $update_data);

        $this->assertDatabaseHas('terms', $update_data);
        $response->assertRedirect(route('admin.terms.index'));
    }
}