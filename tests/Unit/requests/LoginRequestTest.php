<?php

namespace Tests\Unit\requests;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LoginRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createApplication();
    }

     #[Test]
    public function it_has_the_correct_validation_rules()
    {
        $request = new LoginRequest();

        $rules = $request->rules();

        $this->assertEquals([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], $rules);
    }

     #[Test]
    public function it_passes_validation_with_valid_data()
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, (new LoginRequest())->rules());

        $this->assertTrue($validator->passes());
    }

     #[Test]
    public function it_fails_validation_when_email_is_missing()
    {
        $data = [
            'password' => 'password123',
        ];

        $validator = Validator::make($data, (new LoginRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

     #[Test]
    public function it_fails_validation_when_password_is_missing()
    {
        $data = [
            'email' => 'test@example.com',
        ];

        $validator = Validator::make($data, (new LoginRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

     #[Test]
    public function it_fails_validation_with_invalid_email_format()
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, (new LoginRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}
