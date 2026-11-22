<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/order-items', tags: ['Order items'], summary: 'List order items', responses: [
    new OA\Response(response: 200, description: 'Order item collection.', content: new OA\JsonContent(ref: '#/components/schemas/OrderItemCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/order-items', tags: ['Order items'], summary: 'Create an order item', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateOrderItem'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/order-items/{id}', tags: ['Order items'], summary: 'Get an order item', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Order item record.', content: new OA\JsonContent(ref: '#/components/schemas/OrderItemEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/order-items/{id}', tags: ['Order items'], summary: 'Update an order item', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateOrderItem'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated order item record.', content: new OA\JsonContent(ref: '#/components/schemas/OrderItemEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/order-items/{id}', tags: ['Order items'], summary: 'Delete an order item', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive order item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class OrderItemRoutes
{
    public static function register(OrderItemService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('order-items', $service, $logger);
    }
}
