<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiRequestService
{
    private Client $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * Send a rate-limited API request.
     *
     * @param string $key Rate limiter key.
     * @param string $method HTTP method (GET, POST, etc.).
     * @param string $url API endpoint URL.
     * @param array $options Guzzle HTTP options.
     * @return array Response data or an empty array on failure.
     */
    public function sendRequest(string $key, string $method, string $url, array $options = []): array
    {
        $rateLimitReached = !RateLimiter::attempt(
            $key,
            10,
            function () {
                return true;
            }
        );

        if ($rateLimitReached) {
            Log::info("Rate limit reached for key: {$key}. Throttling...");
            return [];
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            Log::info("Request successful for rate limit key: {$key}.");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error("API request failed: {$e->getMessage()}");
            return [];
        }
    }
}
