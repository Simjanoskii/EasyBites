<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/v1/reviews', tags: ['Reviews'], summary: 'List reviews', responses: [
    new OA\Response(response: 200, description: 'Review collection.', content: new OA\JsonContent(ref: '#/components/schemas/ReviewCollection')),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Post(path: '/api/v1/reviews', tags: ['Reviews'], summary: 'Create a review', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/CreateReview'), responses: [
    new OA\Response(ref: '#/components/responses/Created', response: '201'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Get(path: '/api/v1/reviews/{id}', tags: ['Reviews'], summary: 'Get a review', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive review record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Review record.', content: new OA\JsonContent(ref: '#/components/schemas/ReviewEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Put(path: '/api/v1/reviews/{id}', tags: ['Reviews'], summary: 'Update a review', requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateReview'), parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive review record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(response: 200, description: 'Updated review record.', content: new OA\JsonContent(ref: '#/components/schemas/ReviewEnvelope')),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
#[OA\Delete(path: '/api/v1/reviews/{id}', tags: ['Reviews'], summary: 'Delete a review', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Positive review record ID.', schema: new OA\Schema(type: 'integer', minimum: 1)),
], responses: [
    new OA\Response(ref: '#/components/responses/Deleted', response: '200'),
    new OA\Response(ref: '#/components/responses/BadRequest', response: '400'),
    new OA\Response(ref: '#/components/responses/NotFound', response: '404'),
    new OA\Response(ref: '#/components/responses/ServerError', response: '500'),
])]
final class ReviewRoutes
{
    public static function register(ReviewService $service, ApiLogger $logger): void
    {
        ApiRoutes::registerCrud('reviews', $service, $logger);
    }
}
