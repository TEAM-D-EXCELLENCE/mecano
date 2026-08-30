<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UserMigrationAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('users', [
            'id',
            'name',
            'email',
            'password',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_personal_access_tokens_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('personal_access_tokens'));
    }

    public function test_user_seeder_creates_mechanic_admin_account(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 1);

        $user = User::query()->where('email', 'mecanicien@garage.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Mécanicien Admin', $user->name);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_seeder_is_idempotent(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 1);
    }
}
