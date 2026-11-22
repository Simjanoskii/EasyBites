<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/orders', tags: ['Orders'], summary: 'List orders', responses: [
    new OA\Response(response: 200, description: 'Order collection.', content: new OA\JsonContent(ref: '#/components/schemas/OrderCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/orders', tags: ['Orders'], summary: 'Create an order', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateOrder'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/orders/{id}', tags: ['Orders'], summary: 'Get an order', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Order record.', content: new OA\JsonContent(ref: '#/components/schemas/OrderEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/orders/{id}', tags: ['Orders'], summary: 'Update an order', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateOrder'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated order record.', content: new OA\JsonContent(ref: '#/components/schemas/OrderEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/orders/{id}', tags: ['Orders'], summary: 'Delete an order', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class OrderRoutes
{
    public static function register(OrderService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('orders', $service, $logger);
    }
}
