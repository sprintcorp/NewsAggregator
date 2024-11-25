<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Http\Repositories\Eloquent\UserRepository;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /** @test */
    public function it_creates_a_user()
    {
        $data = ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => bcrypt('password')];
        $user = $this->repository->createUser($data);

        $this->assertNotNull($user);
        $this->assertEquals('john@example.com', $user->email);
    }

    /** @test */
    public function it_finds_a_user_by_email()
    {
        $user = User::factory()->create(['email' => 'jane@example.com']);
        $result = $this->repository->findByEmail('jane@example.com');

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }
}
