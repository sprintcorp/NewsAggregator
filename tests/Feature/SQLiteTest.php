<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class SQLiteTest extends TestCase
{
    use RefreshDatabase;

     #[Test]
    public function it_uses_sqlite_for_testing()
    {
        // Create a test user
        $user = User::factory()->create(['name' => 'Test User']);

        // Assert the user exists in the database
        $this->assertDatabaseHas('users', ['name' => 'Test User']);
    }
}
