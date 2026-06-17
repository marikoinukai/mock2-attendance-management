<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     *
     * @return void
     */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'メール認証テスト',
            'email' => 'verify@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'verify@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);

        $response->assertRedirect('/attendance');
    }

    /**
     * 未認証ユーザーは勤怠登録画面にアクセスできず、メール認証画面へ遷移する
     *
     * @return void
     */
    public function test_unverified_user_is_redirected_to_email_verification_notice(): void
    {
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    /**
     * 認証URLにアクセスするとメール認証が完了し、勤怠登録画面へ遷移する
     *
     * @return void
     */
    public function test_user_can_verify_email(): void
    {
        Event::fake();

        $user = User::create([
            'name' => '認証確認ユーザー',
            'email' => 'verified@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        Event::assertDispatched(Verified::class);

        $response->assertRedirect('/attendance?verified=1');
    }
}