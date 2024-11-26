<?php

namespace Tests\Feature\Services;

use App\Http\Services\AuthService;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use Carbon\Carbon;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;

class AuthServiceFeatureTest extends TestCase
{
    use RefreshDatabase;

    private $authService;
    /**
     * @var \Mockery\MockInterface&\App\Http\Repositories\Contracts\UserRepositoryInterface
     */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the UserRepositoryInterface
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    #[Test]
    public function register_creates_user_with_hashed_password()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $hashedData = array_merge($data, ['password' => Hash::make($data['password'])]);

        $this->userRepository->shouldReceive('createUser')
            ->once()
            ->with(Mockery::on(function ($userData) use ($data) {
                return $userData['email'] === $data['email'] && Hash::check($data['password'], $userData['password']);
            }))
            ->andReturn(new User($hashedData));

        $user = $this->authService->register($data);

        $this->assertEquals($data['email'], $user->email);
    }

    #[Test]
    public function login_returns_token_on_successful_authentication()
    {
        $password = 'password123';
        $hashedPassword = Hash::make($password);
        $user = User::factory()->make(['password' => $hashedPassword]);
        $user->forceFill(['id' => 1]);

        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with($user->email)
            ->andReturn($user);


        $result = $this->authService->login($user->email, $password);

        $this->assertNotEmpty($result);
    }

    #[Test]
    public function login_returns_null_on_invalid_credentials()
    {
        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('invalid@example.com')
            ->andReturn(null);

        $token = $this->authService->login('invalid@example.com', 'wrongpassword');

        $this->assertNull($token);
    }

    #[Test]
    public function logout_deletes_user_tokens()
    {
        $user = User::factory()->create();
        $user->tokens()->create(['name' => 'API Token', 'token' => 'fake-token']);

        $this->authService->logout($user);

        $this->assertEmpty($user->tokens);
    }

    #[Test]
public function reset_password_updates_otp_and_expiration()
{
    $mockUser = Mockery::mock(User::class)->makePartial();
    $mockUser->email = 'test@example.com';
    $mockUser->otp = 't12345';

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->with($mockUser->email)
        ->andReturn($mockUser);

    $mockUser->shouldReceive('update')
        ->once()
        ->with(Mockery::on(function ($data) {
            return isset($data['otp'], $data['otp_expiration']) &&
                strlen($data['otp']) === 6 &&
                Carbon::parse($data['otp_expiration'])->greaterThan(Carbon::now());
        }))
        ->andReturnTrue();

    $this->authService->resetPassword(['email' => $mockUser->email]);

    $this->assertTrue(true);
}


    #[Test]
    public function update_password_successfully_updates_password_with_valid_otp()
    {
        $user = User::factory()->make([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expiration' => Carbon::now()->addMinutes(5),
        ]);

        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with($user->email)
            ->andReturn($user);

        $response = $this->authService->updatePassword([
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'newpassword',
        ]);

        $this->assertTrue($response['status']);
        $this->assertEquals('Password updated successfully.', $response['message']);
    }

    #[Test]
    public function update_password_fails_with_invalid_otp()
    {
        $user = User::factory()->make([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expiration' => Carbon::now()->addMinutes(5),
        ]);

        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with($user->email)
            ->andReturn($user);

        $response = $this->authService->updatePassword([
            'email' => $user->email,
            'otp' => '654321',
            'password' => 'newpassword',
        ]);

        $this->assertFalse($response['status']);
        $this->assertEquals('Invalid OTP.', $response['message']);
    }

    #[Test]
    public function update_password_fails_when_otp_is_expired()
    {
        $user = User::factory()->make([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expiration' => Carbon::now()->subMinutes(1),
        ]);

        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with($user->email)
            ->andReturn($user);

        $response = $this->authService->updatePassword([
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'newpassword',
        ]);

        $this->assertFalse($response['status']);
        $this->assertEquals('OTP has expired.', $response['message']);
    }

    #[Test]
    public function update_password_fails_when_user_not_found()
    {
        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('notfound@example.com')
            ->andReturn(null);

        $response = $this->authService->updatePassword([
            'email' => 'notfound@example.com',
            'otp' => '123456',
            'password' => 'newpassword',
        ]);

        $this->assertFalse($response['status']);
        $this->assertEquals('User not found.', $response['message']);
    }

    private function mockTokenCreation($user, $token)
    {
        $user->tokens = collect(); // Mock token relationship
        $user->tokens->add((object) ['plainTextToken' => $token]);
    }
}
