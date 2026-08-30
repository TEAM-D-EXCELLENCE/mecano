<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_DEFAULT_EMAIL', 'mecanicien@garage.com')],
            [
                'name' => env('ADMIN_DEFAULT_NAME', 'Mécanicien Admin'),
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'secret123')),
                'email_verified_at' => now(),
            ]
        );
    }
}
