<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/restaurants', tags: ['Restaurants'], summary: 'List restaurants', responses: [
    new OA\Response(response: 200, description: 'Restaurant collection.', content: new OA\JsonContent(ref: '#/components/schemas/RestaurantCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/restaurants', tags: ['Restaurants'], summary: 'Create a restaurant', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateRestaurant'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/restaurants/{id}', tags: ['Restaurants'], summary: 'Get a restaurant', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive restaurant record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Restaurant record.', content: new OA\JsonContent(ref: '#/components/schemas/RestaurantEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/restaurants/{id}', tags: ['Restaurants'], summary: 'Update a restaurant', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateRestaurant'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive restaurant record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated restaurant record.', content: new OA\JsonContent(ref: '#/components/schemas/RestaurantEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/restaurants/{id}', tags: ['Restaurants'], summary: 'Delete a restaurant', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive restaurant record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class RestaurantRoutes
{
    public static function register(RestaurantService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('restaurants', $service, $logger);
    }
}
