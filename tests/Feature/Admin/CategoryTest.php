<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /******************************************
     * カテゴリ一覧ページ
     ******************************************/
    // 未ログインのユーザーは管理者側のカテゴリ一覧ページにアクセスできない
    public function test_guest_cannot_access_category_index_page(): void
    {
        $this->get(route('admin.categories.index'));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側のカテゴリ一覧ページにアクセスできない
    public function test_general_user_cannnot_access_category_index_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.categories.index'));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側のカテゴリ一覧ページにアクセスできる
    public function test_admin_user_can_access_category_index_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.categories.index'));
        $response->assertStatus(200);
    }

    /******************************************
     * カテゴリ登録機能
     ******************************************/
    // 未ログインのユーザーはカテゴリを登録できない
    public function test_guest_cannot_store_category()
    {
        // 登録用データ
        $category = [
            'name' => 'テスト',
        ];

        // 登録
        $response = $this->post(route('admin.categories.store'), $category);

        $this->assertDatabaseMissing('categories', $category);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを登録できない
    public function test_general_user_cannot_store_category()
    {
        // 登録用データ
        $category = [
            'name' => 'テスト',
        ];

        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 登録
        $response = $this->actingAs($generalUser)->post(route('admin.categories.store'), $category);

        $this->assertDatabaseMissing('categories', $category);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを登録できる
    public function test_admin_user_can_store_category()
    {
        // 登録用データ
        $category = [
            'name' => 'テスト',
        ];

        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 登録
        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), $category);

        $this->assertDatabaseHas('categories', $category);
        $response->assertRedirect(route('admin.categories.index'));
    }

    /******************************************
     * カテゴリ更新機能
     ******************************************/
    // 未ログインのユーザーはカテゴリを更新できない
    public function test_guest_cannot_update_category(): void
    {
        // テスト用ダミーデータ
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'テスト',
        ];

        // 更新
        $response = $this->patch(route('admin.categories.update', $category), $updateData);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを更新できない
    public function test_general_user_cannot_update_category(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'テスト',
        ];

        // 更新
        $response = $this->actingAs($generalUser)->patch(route('admin.categories.update', $category), $updateData);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを更新できる
    public function test_admin_user_can_update_category(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'テスト',
        ];

        // 更新
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.categories.update', $category), $updateData);
        $response->assertRedirect(route('admin.categories.index'));
    }

    /******************************************
     * カテゴリ削除機能
     ******************************************/
    // 未ログインのユーザーはカテゴリを削除できない
    public function test_guest_cannot_destroy_restaurant(): void
    {
        // テスト用ダミーデータ
        $category = Category::factory()->create();

        // 削除
        $response = $this->delete(route('admin.categories.destroy', $category));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを削除できない
    public function test_general_user_cannot_destroy_restaurant(): void
    {

        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $category = Category::factory()->create();

        // 削除
        $response = $this->actingAs($generalUser)->delete(route('admin.categories.destroy', $category));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを削除できる
    public function admin_user_cannot_destroy_restaurant(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $category = Category::factory()->create();

        // 削除
        $response = $this->actingAs($admin, 'admin')->delete(route('admin.categories.destroy', $category));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.categories.index'));
    }
}