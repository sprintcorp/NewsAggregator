<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdatePasswordRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

     #[Test]
    public function it_has_the_correct_validation_rules()
    {
        $rules = (new UpdatePasswordRequest())->rules();

        $this->assertEquals([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'otp' => 'required|string|min:6',
        ], $rules);
    }

     #[Test]
    public function it_passes_validation_with_valid_data()
    {
        $data = [
            'email' => 'jane@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'otp' => '123456',
        ];
        $validator = Validator::make($data, (new UpdatePasswordRequest())->rules());

        $this->assertTrue($validator->passes());
    }

     #[Test]
    public function it_fails_validation_with_invalid_data()
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'not-matching',
            'otp' => '123',
        ];

        $validator = Validator::make($data, (new UpdatePasswordRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertArrayHasKey('otp', $validator->errors()->toArray());
    }
}
