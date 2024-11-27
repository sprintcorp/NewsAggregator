<?php

namespace Tests\Feature\services;

use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendOtpNotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

     #[Test]
    public function it_sends_otp_notification_via_email()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $otp = '123456';

        $user->notify(new SendOtpNotification($otp));

        Notification::assertSentTo(
            [$user],
            SendOtpNotification::class,
            function (SendOtpNotification $notification, $channels) use ($otp) {
                $this->assertContains('mail', $channels);

                $reflection = new \ReflectionClass($notification);
                $property = $reflection->getProperty('otp');
                $property->setAccessible(true);

                return $property->getValue($notification) === $otp;
            }
        );
    }

     #[Test]
    public function it_formats_email_correctly_with_the_otp()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $otp = '654321';

        $notification = new SendOtpNotification($otp);

        $mailMessage = $notification->toMail($user);

        $this->assertEquals('Your OTP Code', $mailMessage->subject);

        $this->assertStringContainsString('Use the following OTP to complete your request:', $mailMessage->render());
        $this->assertStringContainsString($otp, $mailMessage->render());
        $this->assertStringContainsString('This OTP is valid for 15 minutes.', $mailMessage->render());
        $this->assertStringContainsString('If you did not request this, please ignore this email.', $mailMessage->render());
    }
}
