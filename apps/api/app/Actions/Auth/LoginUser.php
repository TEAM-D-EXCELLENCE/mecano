<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\LoginData;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final readonly class LoginUser
{
    /**
     * Authenticate user and issue a single active Sanctum token.
     *
     * @return array{token: string, user: User}
     *
     * @throws InvalidCredentialsException
     */
    public function handle(LoginData $data): array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            $this->handleFailedAttempt($data);

            throw new InvalidCredentialsException('Identifiants incorrects.');
        }

        // Reset consecutive failures counter on successful login
        Cache::forget("login_failures:{$data->email}");

        // Security rule: single active token (revoke all previous tokens)
        $user->tokens()->delete();

        // Issue new Sanctum token
        $token = $user->createToken('admin-session')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Track and log failed authentication attempts.
     */
    private function handleFailedAttempt(LoginData $data): void
    {
        $cacheKey = "login_failures:{$data->email}";
        $attempts = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(30));

        Log::info('Tentative de connexion échouée', [
            'email' => $data->email,
            'ip' => $data->ip,
            'attempt' => $attempts,
            'timestamp' => now()->toIso8601String(),
        ]);

        if ($attempts >= 5) {
            Log::warning('Cinq échecs de connexion consécutifs détectés pour le compte', [
                'email' => $data->email,
                'ip' => $data->ip,
                'attempts' => $attempts,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
