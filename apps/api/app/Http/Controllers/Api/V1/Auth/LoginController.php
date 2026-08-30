<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginUser;
use App\Data\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    /**
     * Handle the incoming login request.
     */
    public function __invoke(LoginRequest $request, LoginUser $loginUser): JsonResponse
    {
        $result = $loginUser->handle(LoginData::fromRequest($request));

        return response()->json([
            'token' => $result['token'],
            'user' => UserResource::make($result['user']),
        ], 200);
    }
}
