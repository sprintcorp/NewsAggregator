<?php

namespace Tests\Unit\requests;

use App\Http\Requests\Article\FilterArticlesRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FilterArticlesRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

     #[Test]
    public function it_has_the_correct_validation_rules()
    {
        $rules = (new FilterArticlesRequest())->rules();

        $this->assertEquals([
            'keyword' => 'nullable|string',
            'category' => 'nullable|string',
            'source' => 'nullable|string',
            'date' => 'nullable|date',
        ], $rules);
    }

     #[Test]
    public function it_passes_validation_with_valid_data()
    {
        $data = [
            'keyword' => 'technology',
            'category' => 'Health',
            'source' => 'BBC',
            'date' => '2023-11-01',
        ];

        $validator = Validator::make($data, (new FilterArticlesRequest())->rules());

        $this->assertTrue($validator->passes());
    }

     #[Test]
    public function it_fails_validation_with_invalid_data()
    {
        $data = [
            'date' => 'invalid-date',
        ];

        $validator = Validator::make($data, (new FilterArticlesRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }
}
