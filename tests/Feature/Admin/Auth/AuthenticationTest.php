<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;


class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->withViewErrors([])->get('/admin/login', [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        Log::info('Login attempt response status: ' . $response->status());
        Log::info('Login attempt response content: ' . $response->getContent()); // もしエラーメッセージなどあれば
        Log::info('Auth check after login: ' . Auth::guard('admin')->check());

        // 管理者画面にログインしてるか：guard('admin')
        $this->assertTrue(Auth::guard('admin')->check());
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }

    public function test_admins_can_not_authenticate_with_invalid_password(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertGuest();
    }

    public function test_admins_can_logout(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // actingAs(ユーザー, プロバイダ)：config/auth.php > 'guards'
        $response = $this->actingAs($admin, 'admin')->post('/admin/logout', [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
