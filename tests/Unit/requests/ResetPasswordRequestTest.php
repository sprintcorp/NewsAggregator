<?php

namespace Tests\Unit\requests;

use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class ResetPasswordRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

     #[Test]
    public function it_has_the_correct_validation_rules()
    {
        $rules = (new ResetPasswordRequest())->rules();

        $this->assertEquals([
            'email' => 'required|string|email|exists:users,email',
        ], $rules);
    }

     #[Test]
    public function it_passes_validation_with_valid_data()
    {
        User::factory()->create(['email' => 'jane@example.com']);
        $data = [
            'email' => 'jane@example.com',
        ];

        $validator = Validator::make($data, (new ResetPasswordRequest())->rules());

        $this->assertTrue($validator->passes());
    }

     #[Test]
    public function it_fails_validation_with_invalid_data()
    {
        $data = [
            'email' => 'invalid-email',
        ];

        $validator = Validator::make($data, (new ResetPasswordRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}
