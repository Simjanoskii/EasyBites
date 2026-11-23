<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/weather/sarajevo', tags: ['Weather'], summary: 'Get current Sarajevo weather and a dining suggestion', responses: [
    new OA\Response(response: 200, description: 'Normalized current conditions with a weather-aware dining tip.', content: new OA\JsonContent(ref: '#/components/schemas/WeatherEnvelope')),
    new OA\Response(ref: '#/components/responses/ServiceUnavailable', response: '503'),
])]
final class WeatherRoutes
{
    public static function register(WeatherService $weather, ApiLogger $logger): void
    {
        Flight::route('GET /api/v1/weather/sarajevo', static function () use ($weather, $logger): void {
            ApiResponse::run(static function () use ($weather): void {
                Flight::json(['data' => $weather->getSarajevoConditions()], 200);
            }, $logger);
        });
    }
}
