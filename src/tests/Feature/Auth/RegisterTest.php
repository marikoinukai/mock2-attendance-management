<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'user1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_name_must_be_20_characters_or_less()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => str_repeat('あ', 21),
            'email' => 'user1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'お名前は20文字以内で入力してください',
        ]);
    }

    public function test_email_is_required()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'ユーザー1',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_email_must_be_valid_email_format()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'ユーザー1',
            'email' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'メール形式で入力してください',
        ]);
    }

    public function test_password_is_required()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_password_must_be_confirmed()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => 'password',
            'password_confirmation' => 'different123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('users', [
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
        ]);

        $user = User::where('email', 'user1@example.com')->first();

        $this->assertTrue(Hash::check('password', $user->password));
    }
}
