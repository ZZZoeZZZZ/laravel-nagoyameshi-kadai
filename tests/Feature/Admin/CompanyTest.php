<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CompanyTest extends TestCase
{
    use RefreshDatabase;
    /******************************************
     * 会社概要ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の会社概要ページにアクセスできない
    public function test_guest_cannot_access_company_index_page(): void
    {
        // テスト用ダミーデータ
        Company::factory()->create();
        
        $this->get(route('admin.company.index'));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の会社概要ページにアクセスできない
    public function test_general_user_cannnot_access_company_index_page(): void
    {
        // テスト用ダミーデータ
        Company::factory()->create();
        
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.company.index'));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の会社概要ページにアクセスできる
    public function test_admin_user_can_access_companyt_index_page(): void
    {
        // テスト用ダミーデータ
        Company::factory()->create();
        
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.company.index'));
        $response->assertStatus(200);
    }

    /******************************************
     * 会社概要編集ページ
     ******************************************/
    // 未ログインのユーザーは管理者側の会社概要編集ページにアクセスできない
    public function test_guest_cannot_access_restaurant_edit_page(): void
    {
        // テスト用ダミーデータ
        $company = Company::factory()->create();

        $this->get(route('admin.company.edit', ['company' =>  $company->id]));
        $this->assertGuest();
    }

    // ログイン済みの一般ユーザーは管理者側の会社概要編集ページにアクセスできない
    public function test_general_user_cannnot_access_restaurant_edit_page(): void
    {
        // 一般ユーザー作成
        $generalUser = User::factory()->create();

        // テスト用ダミーデータ
        $company = Company::factory()->create();

        // 一般ユーザーでアクセス
        $response = $this->actingAs($generalUser)->get(route('admin.company.edit', ['company' =>  $company->id]));
        $response->assertRedirect('/admin/login');
    }

    // ログイン済みの管理者は管理者側の会社概要編集ページにアクセスできる
    public function test_admin_user_can_access_restaurant_edit_page(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $company = Company::factory()->create();

        // 管理者でアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.company.edit', ['company' => $company->id]));
        $response->assertStatus(200);
    }

    /******************************************
     * 会社概要更新機能
     ******************************************/
    // 未ログインのユーザーは会社概要を更新できない
    public function test_guest_cannot_update_company(): void
    {
        // テスト用ダミーデータ
        $company = Company::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'representative' => 'テスト更新',
            'establishment_date' => 'テスト更新',
            'capital' => 'テスト更新',
            'business' => 'テスト更新',
            'number_of_employees' => 'テスト更新',
        ];

        // 更新
        $response = $this->patch(route('admin.company.update', $company), $update_data);

        $this->assertDatabaseMissing('companies', $update_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは会社概要を更新できない
    public function test_general_user_cannot_update_company(): void
    {
        // 一般ユーザー作成
        $general_user = User::factory()->create();

        // テスト用ダミーデータ
        $company = Company::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'representative' => 'テスト更新',
            'establishment_date' => 'テスト更新',
            'capital' => 'テスト更新',
            'business' => 'テスト更新',
            'number_of_employees' => 'テスト更新',
        ];

        // 更新
        $response = $this->actingAs($general_user)->patch(route('admin.company.update', $company), $update_data);

        $this->assertDatabaseMissing('companies', $update_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は会社概要を更新できる
    public function test_admin_user_can_update_company(): void
    {
        // 管理者作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用ダミーデータ
        $company = Company::factory()->create();
        $update_data = [
            'name' => 'テスト更新',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'representative' => 'テスト更新',
            'establishment_date' => 'テスト更新',
            'capital' => 'テスト更新',
            'business' => 'テスト更新',
            'number_of_employees' => 'テスト更新',
        ];

        // 更新
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.company.update', $company), $update_data);

        $this->assertDatabaseHas('companies', $update_data);
        $response->assertRedirect(route('admin.company.index'));
    }
}