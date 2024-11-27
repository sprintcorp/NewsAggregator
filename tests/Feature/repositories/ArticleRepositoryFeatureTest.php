<?php

namespace Tests\Feature\repositories;

use Tests\TestCase;
use App\Models\Article;
use App\Models\Preference;
use App\Http\Repositories\Eloquent\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ArticleRepositoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository();
    }

     #[Test]
    public function it_fetches_all_articles_with_filters()
    {
        Article::factory()->create(['title' => 'Laravel Basics', 'category' => 'Technology']);
        Article::factory()->create(['title' => 'Health Tips', 'category' => 'Health']);
        Article::factory()->create(['title' => 'Sports News', 'category' => 'Sports']);

        $result = $this->repository->getAll(['keyword' => 'Laravel'], 10);
        $this->assertCount(1, $result->items());
        $this->assertEquals('Laravel Basics', $result->items()[0]['title']);

        $result = $this->repository->getAll(['category' => 'Health'], 10);
        $this->assertCount(1, $result->items());
        $this->assertEquals('Health Tips', $result->items()[0]['title']);

        $result = $this->repository->getAll([], 10);
        $this->assertCount(3, $result->items());
    }

     #[Test]
    public function it_fetches_an_article_by_id()
    {
        $article = Article::factory()->create(['title' => 'Specific Article']);

        $result = $this->repository->findById($article->id);

        $this->assertNotNull($result);
        $this->assertEquals('Specific Article', $result->title);

        $result = $this->repository->findById(9999);
        $this->assertNull($result);
    }

     #[Test]
    public function it_fetches_articles_based_on_preferences()
    {
        $preferences = Preference::factory()->create([
            'category' => ['Technology', 'Health'],
            'author' => ['John Doe', 'Jane Smith'],
            'source' => ['Tech Times', 'Health Daily'],
        ]);

        Article::factory()->create(['category' => 'Technology', 'author' => 'John Doe', 'source' => 'Tech Times']);
        Article::factory()->create(['category' => 'Sports', 'author' => 'Jane Doe', 'source' => 'Sports News']);
        Article::factory()->create(['category' => 'Health', 'author' => 'Jane Smith', 'source' => 'Health Daily']);

        $result = $this->repository->getArticlesByPreferences($preferences, 10);

        $this->assertCount(2, $result->items());
        $this->assertEquals('Health', $result->items()[0]['category']);
        $this->assertEquals('Technology', $result->items()[1]['category']);
    }
}
