<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;
use App\Models\Preference;
use App\Http\Repositories\Eloquent\ArticleRepository;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository();
    }

    /** @test */
    public function it_fetches_all_articles_with_filters()
    {
        Article::factory()->create(['category' => 'Technology', 'author' => 'Alice']);
        Article::factory()->create(['category' => 'Health', 'author' => 'Bob']);

        $filters = ['category' => 'Technology'];

        $result = $this->repository->getAll($filters, 10);

        // Cast to a collection for clarity
        $firstItem = collect($result->items())->first();

        $this->assertCount(1, $result->items());
        $this->assertEquals('Technology', $firstItem['category']);
    }



    /** @test */
    public function it_finds_an_article_by_id()
    {
        $article = Article::factory()->create(['title' => 'Test Article']);
        $result = $this->repository->findById($article->id);

        $this->assertNotNull($result);
        $this->assertEquals('Test Article', $result->title);
    }

    /** @test */
    public function it_fetches_articles_based_on_preferences()
    {
        $preferences = Preference::factory()->create([
            'category' => ['Technology', 'Health'],
        ]);

        Article::factory()->create(['category' => 'Technology']);
        Article::factory()->create(['category' => 'Sports']);

        $result = $this->repository->getArticlesByPreferences($preferences, 10);
        dd($result);
        $this->assertCount(1, $result->items());
        $this->assertEquals('Technology', $result->items()[0]['category']);
    }
}
