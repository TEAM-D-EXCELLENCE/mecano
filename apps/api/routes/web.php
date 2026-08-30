<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'ok',
        'documentation_url' => url('/docs'),
    ]);
});

// Interactive Scalar API Documentation
Route::get('/docs', function () {
    return view('docs');
})->name('docs.index');

Route::get('/docs/openapi.yaml', function () {
    $path = base_path('../../openapi.yaml');
    if (! file_exists($path)) {
        $path = base_path('openapi.yaml');
    }
    if (! file_exists($path)) {
        $path = public_path('openapi.yaml');
    }

    abort_unless(file_exists($path), 404, 'Contrat OpenAPI introuvable.');

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'text/yaml; charset=utf-8',
        'Cache-Control' => 'no-cache, private',
    ]);
})->name('docs.openapi');
