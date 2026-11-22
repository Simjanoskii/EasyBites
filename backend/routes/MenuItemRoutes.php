<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/menu-items', tags: ['Menu items'], summary: 'List menu items', responses: [
    new OA\Response(response: 200, description: 'Menu item collection.', content: new OA\JsonContent(ref: '#/components/schemas/MenuItemCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/menu-items', tags: ['Menu items'], summary: 'Create a menu item', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateMenuItem'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/menu-items/{id}', tags: ['Menu items'], summary: 'Get a menu item', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive menu item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Menu item record.', content: new OA\JsonContent(ref: '#/components/schemas/MenuItemEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/menu-items/{id}', tags: ['Menu items'], summary: 'Update a menu item', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateMenuItem'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive menu item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated menu item record.', content: new OA\JsonContent(ref: '#/components/schemas/MenuItemEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/menu-items/{id}', tags: ['Menu items'], summary: 'Delete a menu item', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive menu item record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class MenuItemRoutes
{
    public static function register(MenuItemService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('menu-items', $service, $logger);
    }
}
