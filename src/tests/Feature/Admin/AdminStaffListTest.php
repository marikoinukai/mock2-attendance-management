<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin($name = '管理者')
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }

    private function createStaff($name, $email)
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_view_staff_list()
    {
        $admin = $this->createAdmin();

        $this->createStaff('ユーザー1', 'yamada@example.com');
        $this->createStaff('ユーザー2', 'sato@example.com');

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('yamada@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('sato@example.com');
        $response->assertSee('詳細');
    }

    public function test_staff_list_does_not_display_admin_users()
    {
        $admin = $this->createAdmin('ユーザー3');

        $this->createStaff('一般ユーザー', 'staff@example.com');

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('一般ユーザー');
        $response->assertSee('staff@example.com');
        $response->assertDontSee('ユーザー3');
    }

    public function test_general_user_cannot_view_staff_list()
    {
        $user = $this->createStaff('一般ユーザー', 'staff@example.com');

        $response = $this->actingAs($user)->get('/admin/staff/list');

        $response->assertStatus(403);
    }
}
