<?php

namespace Tests\Feature\repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Preference;
use App\Http\Repositories\Eloquent\PreferenceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PreferenceRepositoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private PreferenceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new PreferenceRepository();
    }

    #[Test]
    public function it_creates_or_updates_preferences()
    {
        $user = User::factory()->create();
        $data = [
            'category' => ['Technology', 'Health'],
            'author' => ['John Doe'],
            'source' => ['Tech Times'],
        ];

        $preference = $this->repository->createOrUpdate($user->id, $data);

        $this->assertInstanceOf(Preference::class, $preference);
        $this->assertEquals(['Technology', 'Health'], $preference->category);
        $this->assertEquals(['John Doe'], $preference->author);
        $this->assertEquals(['Tech Times'], $preference->source);

        $updatedData = [
            'category' => ['Sports'],
            'author' => ['Jane Doe'],
            'source' => ['Sports News'],
        ];
        $updatedPreference = $this->repository->createOrUpdate($user->id, $updatedData);

        $this->assertEquals(['Sports'], $updatedPreference->category);
        $this->assertEquals(['Jane Doe'], $updatedPreference->author);
        $this->assertEquals(['Sports News'], $updatedPreference->source);
    }

    #[Test]
    public function it_finds_preferences_by_user_id()
    {
        $user = User::factory()->create();
        $preferences = Preference::factory()->create([
            'user_id' => $user->id,
            'category' => ['Technology', 'Health'],
            'author' => ['John Doe'],
            'source' => ['Tech Times'],
        ]);

        $foundPreferences = $this->repository->findByUserId($user->id);

        $this->assertInstanceOf(Preference::class, $foundPreferences);
        $this->assertEquals(['Technology', 'Health'], $foundPreferences->category);
        $this->assertEquals(['John Doe'], $foundPreferences->author);
        $this->assertEquals(['Tech Times'], $foundPreferences->source);

        $nonExistentPreferences = $this->repository->findByUserId(9999);
        $this->assertNull($nonExistentPreferences);
    }
}
