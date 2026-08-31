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
        $middleware->append(SecureHeaders::class);

        // Derrière nginx puis un terminateur TLS, `$request->ip()` renvoie
        // l'adresse du proxy si celui-ci n'est pas déclaré. Toutes les
        // limitations de débit deviennent alors globales au lieu d'être par
        // visiteur, et l'empreinte salée des événements s'effondre sur une
        // seule valeur pour tout le trafic.
        //
        // `TRUSTED_PROXIES` accepte une liste d'adresses ou de sous-réseaux ;
        // `*` fait confiance à tout ce qui précède, ce qui n'est acceptable que
        // lorsqu'aucune requête n'atteint le conteneur sans passer par le proxy.
        $middleware->trustProxies(
            at: match ($proxies = (string) env('TRUSTED_PROXIES', '')) {
                '' => null,
                '*' => '*',
                default => array_map(trim(...), explode(',', $proxies)),
            },
        );
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

        // Filet pour toute exception HTTP qui n'aurait pas son propre rendu.
        //
        // Sans lui, une `ConflictHttpException` levée quelque part dans le code
        // tombe dans le fourre-tout ci-dessous : en production elle devient un
        // 500 « SERVER_ERROR », et le front, qui compare sur `error.code`, ne
        // peut plus distinguer un refus métier d'une panne. Le statut réel est
        // conservé, et l'enveloppe reste celle du contrat.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();

            return response()->json([
                'error' => [
                    'code' => $status >= 500 ? 'SERVER_ERROR' : 'HTTP_ERROR',
                    'message' => $e->getMessage() !== ''
                        ? $e->getMessage()
                        : 'La requête n\'a pas pu être traitée.',
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
