<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\PreferenceController;
use App\Http\Requests\Article\StorePreferenceRequest;
use App\Http\Services\PreferenceService;
use App\Http\Responses\ApiResponse;
use App\Http\Transformers\ArticleTransformer;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class PreferenceControllerTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Services\PreferenceService
     */
    private $preferenceService;
    /**
     * @var \Mockery\MockInterface&\App\Http\Transformers\ArticleTransformer
     */
    private $articleTransformer;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preferenceService = Mockery::mock(PreferenceService::class);
        $this->articleTransformer = Mockery::mock(ArticleTransformer::class);
        $this->controller = new PreferenceController($this->preferenceService, $this->articleTransformer);
    }

    public function test_index_success()
    {
        $user = (object) ['id' => 1];
        $request = Request::create('/api/v1/preferences', 'GET', ['per_page' => 5]);
        $request->setUserResolver(fn() => $user);

        $articles = ['data' => ['title' => 'Sample Article']];
        $paginatedData = ['data' => $articles];

        $this->preferenceService
            ->shouldReceive('getArticlesByPreferences')
            ->with($user, 5)
            ->once()
            ->andReturn($articles);

        $this->articleTransformer
            ->shouldReceive('transformPaginated')
            ->with($articles)
            ->once()
            ->andReturn($paginatedData);

        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Articles retrieved successfully.', $response->getData()->message);
    }

    public function test_store_success()
    {
        $user = (object) ['id' => 1];
        $data = ['category' => ['Technology'], 'author' => ['John Doe'], 'source' => ['Tech Times']];

        $request = Mockery::mock(StorePreferenceRequest::class);
        $request->shouldReceive('user')->andReturn($user);
        $request->shouldReceive('validated')->andReturn($data);

        $this->preferenceService
            ->shouldReceive('storePreferences')
            ->with($user, $data)
            ->once()
            ->andReturn($data);

        $response = $this->controller->store($request);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Preferences saved successfully.', $response->getData()->message);
    }

    public function test_show_no_preferences()
    {
        $user = (object) ['id' => 1];
        $request = Request::create('/api/v1/prefered/article', 'GET');
        $request->setUserResolver(fn() => $user);

        $this->preferenceService
            ->shouldReceive('getPreferences')
            ->with($user)
            ->once()
            ->andReturn(null);

        $response = $this->controller->show($request);

        $this->assertEquals(404, $response->status());
        $this->assertEquals('No preferences found.', $response->getData()->message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
