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
        $user = User::factory()->create(['name' => 'Test User']);

        $this->assertDatabaseHas('users', ['name' => 'Test User']);
    }
}
