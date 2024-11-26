<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Notifications\SendOtpNotification;
use Illuminate\Notifications\Messages\MailMessage;
use PHPUnit\Framework\Attributes\Test;

class SendOtpNotificationTest extends TestCase
{
     #[Test]
     public function it_sends_an_otp_via_email()
     {
         $otp = '123456';
         $notification = new SendOtpNotification($otp);
         $notifiable = new class {
             public $email = 'test@example.com';
         };
         $mailMessage = $notification->toMail($notifiable);
         $this->assertInstanceOf(MailMessage::class, $mailMessage);
         $this->assertEquals('Your OTP Code', $mailMessage->subject);

         $this->assertStringContainsString('Use the following OTP to complete your request:', implode("\n", $mailMessage->introLines));
         $this->assertStringContainsString($otp, implode("\n", $mailMessage->introLines));
         $this->assertStringContainsString('This OTP is valid for 15 minutes.', implode("\n", array_merge($mailMessage->introLines, $mailMessage->outroLines)));
     }

     #[Test]
    public function it_uses_the_mail_channel()
    {
        $otp = '123456';
        $notification = new SendOtpNotification($otp);
        $channels = $notification->via(new class {});
        $this->assertEquals(['mail'], $channels);
    }
}
