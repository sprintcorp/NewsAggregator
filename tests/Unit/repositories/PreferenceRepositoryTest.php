<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Preference;
use App\Models\User;
use App\Http\Repositories\Eloquent\PreferenceRepository;

class PreferenceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PreferenceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PreferenceRepository();
    }

    /** @test */
    public function it_creates_or_updates_preferences()
    {
        $user = User::factory()->create(); // Create a user
        $data = ['category' => ['Technology', 'Health']]; // Match schema

        $this->repository->createOrUpdate($user->id, $data);

        $this->assertDatabaseHas('preferences', [
            'user_id' => $user->id,
            'category' => json_encode($data['category']), // Match JSON format
        ]);
    }

    /** @test */
    public function it_finds_preferences_by_user_id()
    {
        $user = User::factory()->create(); // Ensure the user exists
        $preferences = Preference::factory()->create(['user_id' => $user->id]);

        $result = $this->repository->findByUserId($user->id);

        $this->assertNotNull($result);
        $this->assertEquals($preferences->id, $result->id);
    }
}
