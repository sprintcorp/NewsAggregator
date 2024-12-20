<?php

namespace Tests\Unit\services;

use Tests\TestCase;
use App\Http\Services\ArticleService;
use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Article;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ArticleServiceTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Repositories\Contracts\ArticleRepositoryInterface
     */
    private $articleRepositoryMock;

    private ArticleService $articleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->articleRepositoryMock = Mockery::mock(ArticleRepositoryInterface::class);
        $this->articleService = new ArticleService($this->articleRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_returns_filtered_articles()
    {
        $filters = ['category' => 'Technology'];
        $perPage = 10;
        $page = 1;
        $cacheKey = 'articles:filters:' . md5(json_encode($filters)) . ':per_page:' . $perPage . ':page:' . $page;

        $paginator = new LengthAwarePaginator(['article1', 'article2'], 2, $perPage);

        $cacheTagsMock = Mockery::mock();
        $cacheTagsMock
            ->shouldReceive('remember')
            ->once()
            ->with($cacheKey, Mockery::type('DateTime'), Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) use ($paginator) {
                return $callback();
            });

        Cache::shouldReceive('tags')
            ->once()
            ->with(['articles'])
            ->andReturn($cacheTagsMock);

        $this->articleRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with($filters, $perPage)
            ->andReturn($paginator);

        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        $this->assertEquals($paginator, $articles);
    }


    #[Test]
    public function it_caches_filtered_articles()
    {
        $filters = ['category' => 'Technology'];
        $perPage = 10;
        $page = 1;
        $cacheKey = 'articles:filters:' . md5(json_encode($filters)) . ':per_page:' . $perPage . ':page:' . $page;

        $paginator = new LengthAwarePaginator(['article1', 'article2'], 2, $perPage);

        $cacheTagsMock = Mockery::mock();
        $cacheTagsMock
            ->shouldReceive('remember')
            ->once()
            ->with($cacheKey, Mockery::type('DateTime'), Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) use ($paginator) {
                return $callback();
            });

        Cache::shouldReceive('tags')
            ->once()
            ->with(['articles'])
            ->andReturn($cacheTagsMock);

        $this->articleRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with($filters, $perPage)
            ->andReturn($paginator);

        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        $this->assertEquals($paginator, $articles);
    }



    #[Test]
    public function it_returns_article_by_id()
    {
        $articleId = 1;
        $article = new Article(['id' => $articleId, 'title' => 'Test Article']);
        $this->articleRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($articleId)
            ->andReturn($article);
        $result = $this->articleService->getArticleById($articleId);
        $this->assertEquals($article, $result);
    }

    #[Test]
    public function it_throws_exception_when_article_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Article not found.');
        $articleId = 999;
        $this->articleRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($articleId)
            ->andReturn(null);

        $this->articleService->getArticleById($articleId);
    }
}
