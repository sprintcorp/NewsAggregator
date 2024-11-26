<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Preference;
use App\Models\Article;
use App\Http\Services\PreferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PreferenceServiceFeatureTest extends TestCase
{
    use RefreshDatabase;

    private PreferenceService $preferenceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preferenceService = app(PreferenceService::class);
    }

     #[Test]
    public function it_stores_user_preferences()
    {
        $user = User::factory()->create();

        $preferences = [
            'category' => ['Technology', 'Health'],
            'author' => ['John Doe', 'Jane Doe'],
            'source' => ['The Guardian', 'The New York Times'],
        ];

        $result = $this->preferenceService->storePreferences($user, $preferences);
        $this->assertDatabaseHas('preferences', [
            'user_id' => $user->id,
            'category' => json_encode($preferences['category']),
            'author' => json_encode($preferences['author']),
            'source' => json_encode($preferences['source']),
        ]);

        $this->assertNotNull($result);
    }

     #[Test]
    public function it_fetches_user_preferences()
    {
        $user = User::factory()->create();
        $preferences = Preference::factory()->create(['user_id' => $user->id]);
        $result = $this->preferenceService->getPreferences($user);

        $this->assertNotNull($result);
        $this->assertEquals($preferences->id, $result->id);
        $this->assertEquals($preferences->category, $result->category);
        $this->assertEquals($preferences->author, $result->author);
        $this->assertEquals($preferences->source, $result->source);
    }

     #[Test]
    public function it_fetches_articles_based_on_user_preferences()
    {
        $user = User::factory()->create();
        $preferences = Preference::factory()->create([
            'user_id' => $user->id,
            'category' => ['Technology'],
            'author' => ['John Doe'],
            'source' => ['The Guardian'],
        ]);

        Article::factory()->create(['category' => 'Technology', 'author' => 'John Doe', 'source' => 'The Guardian']);
        Article::factory()->create(['category' => 'Health', 'author' => 'Jane Doe', 'source' => 'The New York Times']);

        $result = $this->preferenceService->getArticlesByPreferences($user, 10);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Technology', $result->items()[0]->category);
    }

     #[Test]
    public function it_returns_message_when_user_has_no_preferences()
    {
        $user = User::factory()->create();

        $result = $this->preferenceService->getArticlesByPreferences($user, 10);

        $this->assertEquals('No preferences found for this user.', $result['message']);
        $this->assertEmpty($result['data']);
        $this->assertNull($result['pagination']);
    }
}
