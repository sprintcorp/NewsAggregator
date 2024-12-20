<?php

namespace Tests\Feature\controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\Preference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PreferenceControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('API Token')->plainTextToken;
    }

    #[Test]
    public function it_fetches_a_paginated_personalized_feed()
    {
        $this->user->preferences()->create([
            'category' => ['Technology'],
            'author' => ['John Doe'],
            'source' => ['Tech Times'],
        ]);

        Article::factory()->count(50)->create([
            'category' => 'Technology',
            'author' => 'John Doe',
            'source' => 'Tech Times',
        ]);

        $response = $this->getJson('/api/v1/personalized-feed?per_page=10', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [['id', 'title', 'category', 'author', 'source', 'published_date']],
                'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
            ],
        ]);
    }

    #[Test]
    public function it_returns_404_if_no_personalized_feed_is_found()
    {
        $response = $this->getJson('/api/v1/personalized-feed', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_stores_user_preferences()
    {
        $response = $this->postJson('/api/v1/preferences', [
            'category' => ['Technology'],
            'author' => ['John Doe'],
            'source' => ['Tech Times'],
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Preferences saved successfully.',
        ]);

        $this->assertDatabaseHas('preferences', [
            'user_id' => $this->user->id,
            'category' => json_encode(['Technology']),
        ]);
    }

    #[Test]
    public function it_retrieves_user_preferences()
    {
        $this->user->preferences()->create([
            'category' => ['Technology', 'Health'],
            'author' => ['John Doe'],
            'source' => ['Tech Times'],
        ]);

        $response = $this->getJson('/api/v1/preferences', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Preferences retrieved successfully.',
        ]);
        $this->assertEquals('Technology', $response->json('data.category')[0]);
    }

    #[Test]
    public function it_returns_404_if_no_preferences_are_found()
    {
        $response = $this->getJson('/api/v1/preferences', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
    }
}
