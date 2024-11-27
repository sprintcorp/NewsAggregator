<?php

namespace Tests\Feature\controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

     #[Test]
    public function it_registers_a_user_successfully()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'User registered successfully.',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);
    }

     #[Test]
    public function it_logs_in_a_user_successfully()
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);
        $this->assertEquals('Login successful.', $response->json('message'));
    }

     #[Test]
    public function it_resets_password_successfully()
    {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password reset token sent to email, 15 minutes expiration time.',
        ]);
    }


     #[Test]
     public function it_updates_password_successfully()
     {
         $user = User::factory()->create([
             'email' => 'john.doe@example.com',
             'password' => Hash::make('oldpassword123'),
         ]);

         $user->update([
             'otp' => '123456',
             'otp_expiration' => Carbon::now()->addMinutes(15)
         ]);

         $response = $this->postJson('/api/v1/password/update', [
             'email' => $user->email,
             'otp' => '123456',
             'password' => 'newpassword123',
             'password_confirmation' => 'newpassword123',
         ]);
         $response->assertStatus(200);
         $response->assertJson([
             'success' => true,
             'message' => 'Password updated successfully.',
             'data' => [],
         ]);
     }

    #[Test]
    public function it_fails_to_update_password_with_invalid_email()
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $user->update(['otp' => '123456']);

        $response = $this->postJson('/api/v1/password/update', [
            'email' => 'invalid.email@example.com',
            'otp' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Validation failed.',
        ]);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);

        $this->assertEquals(
            ['The selected email is invalid.'],
            $response->json('errors.email')
        );
    }
}
