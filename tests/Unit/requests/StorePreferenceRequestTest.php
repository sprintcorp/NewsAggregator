<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Article\StorePreferenceRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StorePreferenceRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

     #[Test]
    public function it_has_the_correct_validation_rules()
    {
        $rules = (new StorePreferenceRequest())->rules();

        $this->assertEquals([
            'category' => 'nullable|array',
            'category.*' => 'string|max:255',
            'author' => 'nullable|array',
            'author.*' => 'string|max:255',
            'source' => 'nullable|array',
            'source.*' => 'string|max:255',
        ], $rules);
    }

     #[Test]
    public function it_passes_validation_with_valid_data()
    {
        $data = [
            'category' => ['Tech', 'Health'],
            'author' => ['John Doe', 'Jane Smith'],
            'source' => ['BBC', 'CNN'],
        ];

        $validator = Validator::make($data, (new StorePreferenceRequest())->rules());

        $this->assertTrue($validator->passes());
    }

     #[Test]
    public function it_fails_validation_with_invalid_data()
    {
        $data = [
            'category' => 'Not an array',
            'author' => [123],
            'source' => '',
        ];

        $validator = Validator::make($data, (new StorePreferenceRequest())->rules());

        $this->assertFalse($validator->passes());
    }
}
