<?php

namespace Tests\Unit\Http\Services;

use App\Http\Services\ApiRequestService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class ApiRequestServiceTest extends TestCase
{

    protected $httpClient;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(Client::class)->makePartial();
        $this->service = new ApiRequestService($this->httpClient);
    }

    public function test_send_request_successful_response()
    {
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with('test_key', 10, Mockery::type('Closure'))
            ->andReturn(true);

        $response = new Response(200, [], json_encode(['data' => 'success']));
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('GET', 'https://example.com/api', [])
            ->andReturn($response);

        Log::shouldReceive('info')->once()->with("Request successful for rate limit key: test_key.");

        $result = $this->service->sendRequest('test_key', 'GET', 'https://example.com/api');
        $this->assertEquals(['data' => 'success'], $result);
    }

    public function test_send_request_rate_limit_exceeded()
    {
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with('test_key', 10, Mockery::type('Closure'))
            ->andReturn(false);

        Log::shouldReceive('info')->once()->with("Rate limit reached for key: test_key. Throttling...");

        $result = $this->service->sendRequest('test_key', 'GET', 'https://example.com/api');
        $this->assertEquals([], $result);
    }

    public function test_send_request_handles_exception()
    {
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with('test_key', 10, Mockery::type('Closure'))
            ->andReturn(true);

        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('GET', 'https://example.com/api', [])
            ->andThrow(new RequestException("Error", new Request('GET', 'https://example.com/api')));

        Log::shouldReceive('error')->once()->with("API request failed: Error");

        $result = $this->service->sendRequest('test_key', 'GET', 'https://example.com/api');
        $this->assertEquals([], $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
