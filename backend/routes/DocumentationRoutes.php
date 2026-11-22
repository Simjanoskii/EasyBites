<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/openapi.json',
    tags: ['Documentation'],
    summary: 'Return the generated OpenAPI document',
    responses: [
        new OA\Response(response: 200, description: 'Generated OpenAPI JSON document.'),
        new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
    ]
)]
#[OA\Get(
    path: '/docs/swagger.php',
    tags: ['Documentation'],
    summary: 'Return the generated OpenAPI document for Swagger UI',
    responses: [
        new OA\Response(response: 200, description: 'Generated OpenAPI JSON document.'),
        new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
    ]
)]
#[OA\Get(
    path: '/docs',
    tags: ['Documentation'],
    summary: 'View interactive Swagger UI documentation',
    responses: [
        new OA\Response(response: 200, description: 'Swagger UI HTML page.'),
        new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
    ]
)]
final class DocumentationRoutes
{
    public static function register(ApiLogger $logger): void
    {
        $spec = static function () use ($logger): void {
            ApiResponse::run(static function (): void {
                Flight::response()->header('Content-Type', 'application/json; charset=utf-8')->write(ApiDocumentation::generate());
            }, $logger);
        };
        Flight::route('GET /openapi.json', $spec);
        Flight::route('GET /docs/swagger.php', $spec);
        Flight::route('GET /docs', static function () use ($logger): void {
            ApiResponse::run(static function (): void {
                $path = __DIR__ . '/../public/v1/docs/index.html';
                $html = file_get_contents($path);
                if ($html === false) {
                    throw new RuntimeException('Swagger UI page could not be read.');
                }

                Flight::response()->header('Content-Type', 'text/html; charset=utf-8')->write($html);
            }, $logger);
        });
    }
}
