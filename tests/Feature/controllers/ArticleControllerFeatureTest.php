<?php

namespace Tests\Feature\controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ArticleControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token
        $user = User::factory()->create();
        $this->token = $user->createToken('API Token')->plainTextToken;
    }

    #[Test]
    public function it_fetches_a_paginated_list_of_articles()
    {
        Article::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/articles?per_page=10', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [['id', 'title', 'category', 'author', 'source', 'published_date']],
                'pagination' => ['total', 'per_page', 'current_page', 'last_page', 'next_page_url', 'prev_page_url'],
            ],
        ]);
        $this->assertCount(10, $response->json('data.data'));
    }

    #[Test]
    public function it_fetches_articles_based_on_filters()
    {
        Article::factory()->create(['category' => 'Technology', 'source' => 'Tech Times']);
        Article::factory()->create(['category' => 'Health', 'source' => 'Health Daily']);

        $response = $this->getJson('/api/v1/articles?category=Technology&source=Tech Times', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Technology', $response->json('data.data')[0]['category']);
        $this->assertEquals('Tech Times', $response->json('data.data')[0]['source']);
    }

    #[Test]
    public function it_fetches_a_single_article_by_id()
    {
        $article = Article::factory()->create();

        $response = $this->getJson('/api/v1/articles/' . $article->id, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'title', 'category', 'author', 'source', 'published_date'],
        ]);
        $this->assertEquals($article->id, $response->json('data.id'));
    }

    #[Test]
    public function it_returns_404_if_article_is_not_found()
    {
        $response = $this->getJson('/api/v1/articles/999', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
        $response->assertStatus(404);
    }
}
