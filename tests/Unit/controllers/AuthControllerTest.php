<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\api\v1\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Services\AuthService;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Services\AuthService
     */
    private $authServiceMock;
    private $authController;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the AuthService
        $this->authServiceMock = Mockery::mock(AuthService::class);

        // Create an instance of the controller with the mocked service
        $this->authController = new AuthController($this->authServiceMock);
    }

    #[Test]
    public function it_registers_a_user()
    {
        $request = Mockery::mock(RegisterRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $user = new \App\Models\User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->authServiceMock->shouldReceive('register')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => 'password123',
            ])
            ->andReturn($user);

        $response = $this->authController->register($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('User registered successfully.', $response->getData()->message);
    }

    #[Test]
    public function it_logs_in_a_user()
    {
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('all')->andReturn([
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $this->authServiceMock->shouldReceive('login')
            ->once()
            ->with('john.doe@example.com', 'password123')
            ->andReturn('mocked_token');

        $response = $this->authController->login($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Login successful.', $response->getData()->message);
        $this->assertEquals('mocked_token', $response->getData()->data->token);
    }

    #[Test]
    public function it_resets_password()
    {
        $request = Mockery::mock(ResetPasswordRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'email' => 'john.doe@example.com',
        ]);

        $this->authServiceMock->shouldReceive('resetPassword')
            ->once()
            ->with(['email' => 'john.doe@example.com']);

        $response = $this->authController->resetPassword($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Password reset token sent to email, 15 minutes expiration time.', $response->getData()->message);
    }

    #[Test]
    public function it_updates_password()
    {
        $request = Mockery::mock(UpdatePasswordRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'email' => 'john.doe@example.com',
            'password' => 'newpassword123',
            'otp' => '123456',
        ]);

        $this->authServiceMock->shouldReceive('updatePassword')
            ->once()
            ->with([
                'email' => 'john.doe@example.com',
                'password' => 'newpassword123',
                'otp' => '123456',
            ])
            ->andReturn([
                'status' => true,
                'message' => 'Password updated successfully.',
            ]);

        $response = $this->authController->savePassword($request);
        $this->assertTrue($response->getData(true)['success']);
        $this->assertEquals('Password updated successfully.', $response->getData(true)['message']);
    }
}
