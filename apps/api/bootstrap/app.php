<?php

declare(strict_types=1);

use App\Exceptions\ApiException;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // L'API tourne derrière un reverse proxy (Caddy) qui est le seul point
        // d'entrée : le conteneur n'est pas joignable directement. Sans cela,
        // Laravel voit l'IP du proxy pour tout le monde (les limiteurs de débit
        // par IP deviennent globaux) et croit répondre en http.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->append(SecureHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => true,
        );

        $exceptions->render(function (ApiException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => $e->errorCode(),
                    'message' => $e->getMessage(),
                    'details' => $e->details(),
                ],
            ], $e->statusCode());
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Certains champs sont invalides.',
                    'details' => $e->errors(),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Non authentifié ou jeton invalide.',
                    'details' => null,
                ],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Action non autorisée.',
                    'details' => null,
                ],
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Ressource introuvable.',
                    'details' => null,
                ],
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Méthode HTTP non autorisée pour cette route.',
                    'details' => null,
                ],
            ], 405);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'RATE_LIMITED',
                    'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
                    'details' => null,
                ],
            ], 429);
        });

        /*
         * Les exceptions HTTP de Symfony portent déjà leur statut : 409 pour un
         * quota épuisé, 422 pour un dérivé non prêt. Sans ce rendu, elles
         * tombaient dans le filet générique ci-dessous et sortaient en 500,
         * ce qui rend un refus métier indiscernable d'une panne.
         */
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();

            $code = match ($status) {
                400 => 'BAD_REQUEST',
                409 => 'CONFLICT',
                410 => 'GONE',
                413 => 'PAYLOAD_TOO_LARGE',
                415 => 'UNSUPPORTED_MEDIA_TYPE',
                422 => 'UNPROCESSABLE_ENTITY',
                423 => 'LOCKED',
                default => $status >= 500 ? 'SERVER_ERROR' : 'HTTP_ERROR',
            };

            $message = $e->getMessage();

            return response()->json([
                'error' => [
                    'code' => $code,
                    'message' => $message !== '' ? $message : 'La requête n\'a pas pu être traitée.',
                    'details' => null,
                ],
            ], $status);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if (config('app.debug')) {
                    return null; // Let Laravel render detailed debug trace in local/testing
                }

                return response()->json([
                    'error' => [
                        'code' => 'SERVER_ERROR',
                        'message' => 'Une erreur interne est survenue.',
                        'details' => null,
                    ],
                ], 500);
            }
        });
    })->create();
