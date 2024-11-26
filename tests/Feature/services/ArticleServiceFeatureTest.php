<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ArticleServiceFeatureTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $articleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->articleService = app(ArticleService::class);
    }

     #[Test]
    public function it_returns_filtered_articles()
    {
        Article::factory()->create(['category' => 'Technology']);
        Article::factory()->create(['category' => 'Health']);
        Article::factory()->create(['category' => 'Sports']);

        $filters = ['category' => 'Technology'];
        $perPage = 10;

        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        $this->assertCount(1, $articles->items());
        $this->assertEquals('Technology', $articles->items()[0]->category);
    }

     #[Test]
    public function it_caches_filtered_articles()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        Article::factory()->create(['category' => 'Technology']);

        $filters = ['category' => 'Technology'];
        $perPage = 10;

        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        $this->assertCount(1, $articles->items());
        $this->assertEquals('Technology', $articles->items()[0]->category);
    }

     #[Test]
    public function it_returns_article_by_id()
    {
        $article = Article::factory()->create([
            'title' => 'Sample Article',
        ]);

        $result = $this->articleService->getArticleById($article->id);

        $this->assertEquals($article->id, $result->id);
        $this->assertEquals('Sample Article', $result->title);
    }

     #[Test]
    public function it_throws_exception_when_article_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Article not found.');

        $invalidId = 999;

        $this->articleService->getArticleById($invalidId);
    }
}
