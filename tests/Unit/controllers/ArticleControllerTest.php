<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\api\v1\ArticleController;
use App\Http\Requests\Article\FilterArticlesRequest;
use App\Http\Services\ArticleService;
use App\Http\Transformers\ArticleTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;
use stdClass;
use PHPUnit\Framework\Attributes\Test;

class ArticleControllerTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Services\ArticleService
     */
    private $articleServiceMock;

    /**
     * @var \Mockery\MockInterface&\App\Http\Transformers\ArticleTransformer
     */
    private $articleTransformerMock;

    private $articleController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->articleServiceMock = Mockery::mock(ArticleService::class);

        $this->articleTransformerMock = Mockery::mock(ArticleTransformer::class);

        $this->articleController = new ArticleController($this->articleServiceMock, $this->articleTransformerMock);
    }

     #[Test]
    public function it_fetches_a_paginated_list_of_articles()
    {
        $request = Mockery::mock(FilterArticlesRequest::class);
        $request->shouldReceive('validated')->andReturn(['keyword' => 'news']);
        $request->shouldReceive('input')->with('per_page', 10)->andReturn(10);

        $articles = collect([
            (object) ['id' => 1, 'title' => 'Article 1', 'content' => 'Content 1'],
            (object) ['id' => 2, 'title' => 'Article 2', 'content' => 'Content 2'],
        ]);
        $paginatedArticles = new LengthAwarePaginator($articles, 2, 10);

        $this->articleServiceMock->shouldReceive('getFilteredArticles')
            ->once()
            ->with(['keyword' => 'news'], 10)
            ->andReturn($paginatedArticles);

        $transformedArticles = [
            'data' => $articles->toArray(),
            'pagination' => [
                'total' => 2,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'next_page_url' => null,
                'prev_page_url' => null,
            ],
        ];

        $this->articleTransformerMock->shouldReceive('transformPaginated')
            ->once()
            ->with($paginatedArticles)
            ->andReturn($transformedArticles);

        $response = $this->articleController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->getData(true)['success']);
    }

     #[Test]
    public function it_fetches_a_single_article_by_id()
    {
        $articleId = 1;

        $article = new stdClass();
        $article->id = $articleId;
        $article->title = 'Article 1';
        $article->content = 'Content 1';

        $this->articleServiceMock->shouldReceive('getArticleById')
            ->once()
            ->with($articleId)
            ->andReturn($article);

        $transformedArticle = [
            'id' => $articleId,
            'title' => 'Article 1',
            'content' => 'Content 1',
        ];

        $this->articleTransformerMock->shouldReceive('transformForDetail')
            ->once()
            ->with($article)
            ->andReturn($transformedArticle);

        $response = $this->articleController->show($articleId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
