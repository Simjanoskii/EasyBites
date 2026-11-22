<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/users', tags: ['Users'], summary: 'List users', responses: [
    new OA\Response(response: 200, description: 'User collection.', content: new OA\JsonContent(ref: '#/components/schemas/UserCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/users', tags: ['Users'], summary: 'Create a customer account record', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateUser'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/users/{id}', tags: ['Users'], summary: 'Get a user', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive user record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'User record.', content: new OA\JsonContent(ref: '#/components/schemas/UserEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/users/{id}', tags: ['Users'], summary: 'Update user details', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateUser'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive user record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated user record.', content: new OA\JsonContent(ref: '#/components/schemas/UserEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/users/{id}', tags: ['Users'], summary: 'Delete a user', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive user record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class UserRoutes
{
    public static function register(UserService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('users', $service, $logger);
    }
}
