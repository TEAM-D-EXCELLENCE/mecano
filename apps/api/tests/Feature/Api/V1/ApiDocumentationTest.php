<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class ApiDocumentationTest extends TestCase
{
    public function test_docs_page_is_accessible(): void
    {
        $response = $this->get('/docs');

        $response->assertOk()
            ->assertSee('Documentation API — Mékano')
            ->assertSee('scalar/api-reference');
    }

    public function test_openapi_yaml_is_served_with_correct_content_type(): void
    {
        $response = $this->get('/docs/openapi.yaml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/yaml; charset=utf-8')
            ->assertSee('openapi: 3.0.3')
            ->assertSee('API Mékano');
    }

    public function test_root_endpoint_includes_documentation_url(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['documentation_url']);
    }
}
