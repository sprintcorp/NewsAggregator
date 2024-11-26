<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Http\Services\PreferenceService;
use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Models\User;
use App\Models\Preference;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class PreferenceServiceTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface&\App\Http\Repositories\Contracts\PreferenceRepositoryInterface
     */
    private $preferenceRepositoryMock;

    /**
     * @var \Mockery\MockInterface&\App\Http\Repositories\Contracts\ArticleRepositoryInterface
     */
    private $articleRepositoryMock;

    private PreferenceService $preferenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preferenceRepositoryMock = Mockery::mock(PreferenceRepositoryInterface::class);
        $this->articleRepositoryMock = Mockery::mock(ArticleRepositoryInterface::class);

        $this->preferenceService = new PreferenceService(
            $this->preferenceRepositoryMock,
            $this->articleRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_stores_user_preferences()
    {
        $user = new User();
        $user->setAttribute('id', 1);
        $preferencesData = ['category' => ['Technology', 'Health']];

        $this->preferenceRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($user->id, $preferencesData)
            ->andReturn($preferencesData);

        $result = $this->preferenceService->storePreferences($user, $preferencesData);

        $this->assertEquals($preferencesData, $result);
    }

    #[Test]
    public function it_fetches_user_preferences()
    {
        $user = new User();
        $user->setAttribute('id', 1); // Explicitly set the ID
        $preferences = new Preference(['category' => ['Technology', 'Health']]);

        // Mock the repository behavior
        $this->preferenceRepositoryMock
            ->shouldReceive('findByUserId')
            ->once()
            ->with($user->id)
            ->andReturn($preferences);

        // Call the service method
        $result = $this->preferenceService->getPreferences($user);

        // Assert
        $this->assertEquals($preferences, $result);
    }

    #[Test]
    public function it_fetches_articles_based_on_user_preferences()
    {
        $user = new User();
        $user->setAttribute('id', 1); // Explicitly set the ID
        $preferences = new Preference(['category' => ['Technology', 'Health']]);
        $articles = ['article1', 'article2'];
        $perPage = 10;

        // Mock the preference repository
        $this->preferenceRepositoryMock
            ->shouldReceive('findByUserId')
            ->once()
            ->with($user->id)
            ->andReturn($preferences);

        // Mock the article repository
        $this->articleRepositoryMock
            ->shouldReceive('getArticlesByPreferences')
            ->once()
            ->with($preferences, $perPage)
            ->andReturn($articles);

        // Call the service method
        $result = $this->preferenceService->getArticlesByPreferences($user, $perPage);

        // Assert
        $this->assertEquals($articles, $result);
    }

    #[Test]
    public function it_returns_message_when_user_has_no_preferences()
    {
        $user = new User();
        $user->setAttribute('id', 1); // Explicitly set the ID
        $perPage = 10;

        // Mock the preference repository to return null
        $this->preferenceRepositoryMock
            ->shouldReceive('findByUserId')
            ->once()
            ->with($user->id)
            ->andReturn(null);

        // Call the service method
        $result = $this->preferenceService->getArticlesByPreferences($user, $perPage);

        // Assert
        $this->assertEquals([
            'message' => 'No preferences found for this user.',
            'data' => [],
            'pagination' => null,
        ], $result);
    }
}
