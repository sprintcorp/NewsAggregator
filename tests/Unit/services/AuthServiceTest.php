<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Http\Services\AuthService;
use App\Notifications\SendOtpNotification;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Date;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class AuthServiceTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Repositories\Contracts\UserRepositoryInterface
     */
    private $userRepositoryMock;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_registers_a_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $this->userRepositoryMock->shouldReceive('createUser')
            ->once()
            ->with(Mockery::on(function ($arg) use ($data) {
                return $arg['name'] === $data['name']
                    && $arg['email'] === $data['email']
                    && Hash::check($data['password'], $arg['password']);
            }))
            ->andReturn(new User($data));

        $user = $this->authService->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
    }

    #[Test]
    public function it_logs_in_a_user()
    {
        $user = Mockery::mock(User::class);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        $user->shouldReceive('getAttribute')
            ->with('password')
            ->andReturn(Hash::make('password123'));

        $user->shouldReceive('createToken')
            ->once()
            ->with('API Token')
            ->andReturn((object)['plainTextToken' => 'test_token']);

        $token = $this->authService->login('john@example.com', 'password123');

        $this->assertEquals('test_token', $token);
    }

    #[Test]
    public function it_resets_password_and_sends_otp()
    {
        $email = 'john@example.com';
        $otp = 'ABC123';
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($data) use ($otp, $now) {

                return isset($data['otp']) && strlen($data['otp']) === 6
                    && $data['otp_expiration']->eq($now->addMinutes(15));
            }));

        $user->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $user->shouldReceive('notify')
            ->once()
            ->with(Mockery::type(SendOtpNotification::class));

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $this->authService->resetPassword(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertEquals($otp, $user->otp);
    }


    #[Test]
    public function it_updates_password_with_valid_otp()
    {
        Date::setTestNow(Carbon::now());

        $user = Mockery::mock(User::class);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        $user->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn('ABC123');
        $user->shouldReceive('getAttribute')
            ->with('otp_expiration')
            ->andReturn(Carbon::now()->addMinutes(15));

        $user->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return Hash::check('newpassword', $arg['password']) && $arg['otp'] === null;
            }));

        $response = $this->authService->updatePassword([
            'email' => 'john@example.com',
            'otp' => 'ABC123',
            'password' => 'newpassword',
        ]);

        $this->assertTrue($response['status']);
        $this->assertEquals('Password updated successfully.', $response['message']);
    }

    #[Test]
    public function it_fails_to_update_password_with_invalid_otp()
    {
        $user = Mockery::mock(User::class);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        $user->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn('ABC123');
        $user->shouldReceive('getAttribute')
            ->with('otp_expiration')
            ->andReturn(Carbon::now()->addMinutes(15));

        $response = $this->authService->updatePassword([
            'email' => 'john@example.com',
            'otp' => 'WRONG123',
            'password' => 'newpassword',
        ]);

        $this->assertFalse($response['status']);
        $this->assertEquals('Invalid OTP.', $response['message']);
    }
}
