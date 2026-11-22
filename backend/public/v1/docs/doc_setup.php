<?php

declare(strict_types=1);

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.3',
    info: new OA\Info(
        title: 'EasyBites API',
        version: '1.0.0',
        description: 'Milestone 3 development API for EasyBites. Authentication and role authorization are not part of this milestone; keep the API on a local development server.',
        contact: new OA\Contact(name: 'EasyBites project')
    ),
    servers: [new OA\Server(url: API_BASE_URL, description: 'Current application host and base path')],
    tags: [
        new OA\Tag(name: 'Users'),
        new OA\Tag(name: 'Restaurants'),
        new OA\Tag(name: 'Menu items'),
        new OA\Tag(name: 'Orders'),
        new OA\Tag(name: 'Order items'),
        new OA\Tag(name: 'Reviews'),
        new OA\Tag(name: 'Weather'),
        new OA\Tag(name: 'Documentation'),
    ],
    components: new OA\Components(
        schemas: [
            new OA\Schema(schema: 'ErrorEnvelope', type: 'object', required: ['error'], properties: [
                new OA\Property(property: 'error', type: 'string'),
            ]),
            new OA\Schema(schema: 'CreatedEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', minimum: 1),
                ]),
            ]),
            new OA\Schema(schema: 'DeletedEnvelope', type: 'object', properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Record deleted.'),
            ]),
            new OA\Schema(schema: 'User', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'full_name', type: 'string', maxLength: 120),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 254),
                new OA\Property(property: 'role', type: 'string', enum: ['customer', 'admin']),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ]),
            new OA\Schema(schema: 'CreateUser', type: 'object', required: ['full_name', 'email', 'password'], additionalProperties: false, properties: [
                new OA\Property(property: 'full_name', type: 'string', minLength: 1, maxLength: 120),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 254),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 128, writeOnly: true),
            ]),
            new OA\Schema(schema: 'UpdateUser', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'full_name', type: 'string', minLength: 1, maxLength: 120),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 254),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 128, writeOnly: true),
            ]),
            new OA\Schema(schema: 'Restaurant', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string', maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'address', type: 'string', maxLength: 255),
                new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 32),
                new OA\Property(property: 'is_active', type: 'integer', enum: [0, 1]),
            ]),
            new OA\Schema(schema: 'CreateRestaurant', type: 'object', required: ['name', 'address'], additionalProperties: false, properties: [
                new OA\Property(property: 'name', type: 'string', minLength: 1, maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'address', type: 'string', minLength: 1, maxLength: 255),
                new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 32),
                new OA\Property(property: 'is_active', type: 'boolean', default: true),
            ]),
            new OA\Schema(schema: 'UpdateRestaurant', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'name', type: 'string', minLength: 1, maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'address', type: 'string', minLength: 1, maxLength: 255),
                new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 32),
                new OA\Property(property: 'is_active', type: 'boolean'),
            ]),
            new OA\Schema(schema: 'MenuItem', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'name', type: 'string', maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'category', type: 'string', maxLength: 60),
                new OA\Property(property: 'price', type: 'string', pattern: '^[0-9]+\\.[0-9]{2}$', example: '12.50'),
                new OA\Property(property: 'is_available', type: 'integer', enum: [0, 1]),
            ]),
            new OA\Schema(schema: 'CreateMenuItem', type: 'object', required: ['restaurant_id', 'name', 'category', 'price'], additionalProperties: false, properties: [
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'name', type: 'string', minLength: 1, maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'category', type: 'string', minLength: 1, maxLength: 60),
                new OA\Property(property: 'price', type: 'number', minimum: 0),
                new OA\Property(property: 'is_available', type: 'boolean', default: true),
            ]),
            new OA\Schema(schema: 'UpdateMenuItem', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'name', type: 'string', minLength: 1, maxLength: 150),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 16000),
                new OA\Property(property: 'category', type: 'string', minLength: 1, maxLength: 60),
                new OA\Property(property: 'price', type: 'number', minimum: 0),
                new OA\Property(property: 'is_available', type: 'boolean'),
            ]),
            new OA\Schema(schema: 'Order', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled']),
                new OA\Property(property: 'total', type: 'string', example: '15.00'),
                new OA\Property(property: 'delivery_address', type: 'string', maxLength: 255),
            ]),
            new OA\Schema(schema: 'CreateOrder', type: 'object', required: ['user_id', 'restaurant_id', 'delivery_address'], additionalProperties: false, properties: [
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'], default: 'pending'),
                new OA\Property(property: 'total', type: 'number', minimum: 0, default: 0),
                new OA\Property(property: 'delivery_address', type: 'string', minLength: 1, maxLength: 255),
            ]),
            new OA\Schema(schema: 'UpdateOrder', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled']),
                new OA\Property(property: 'total', type: 'number', minimum: 0),
                new OA\Property(property: 'delivery_address', type: 'string', minLength: 1, maxLength: 255),
            ]),
            new OA\Schema(schema: 'OrderItem', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'order_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'menu_item_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 32767),
                new OA\Property(property: 'unit_price', type: 'string', example: '12.50'),
            ]),
            new OA\Schema(schema: 'CreateOrderItem', type: 'object', required: ['order_id', 'menu_item_id', 'quantity', 'unit_price'], additionalProperties: false, properties: [
                new OA\Property(property: 'order_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'menu_item_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 32767),
                new OA\Property(property: 'unit_price', type: 'number', minimum: 0),
            ]),
            new OA\Schema(schema: 'UpdateOrderItem', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'order_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'menu_item_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 32767),
                new OA\Property(property: 'unit_price', type: 'number', minimum: 0),
            ]),
            new OA\Schema(schema: 'Review', type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
                new OA\Property(property: 'comment', type: 'string', nullable: true, maxLength: 16000),
            ]),
            new OA\Schema(schema: 'CreateReview', type: 'object', required: ['user_id', 'restaurant_id', 'rating'], additionalProperties: false, properties: [
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
                new OA\Property(property: 'comment', type: 'string', nullable: true, maxLength: 16000),
            ]),
            new OA\Schema(schema: 'UpdateReview', type: 'object', minProperties: 1, additionalProperties: false, properties: [
                new OA\Property(property: 'user_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'restaurant_id', type: 'integer', minimum: 1),
                new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
                new OA\Property(property: 'comment', type: 'string', nullable: true, maxLength: 16000),
            ]),
            new OA\Schema(schema: 'Weather', type: 'object', properties: [
                new OA\Property(property: 'location', type: 'object', properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Sarajevo'),
                    new OA\Property(property: 'latitude', type: 'number'),
                    new OA\Property(property: 'longitude', type: 'number'),
                ]),
                new OA\Property(property: 'current', type: 'object', properties: [
                    new OA\Property(property: 'temperature_c', type: 'number'),
                    new OA\Property(property: 'feels_like_c', type: 'number'),
                    new OA\Property(property: 'humidity_percent', type: 'integer', minimum: 0, maximum: 100),
                    new OA\Property(property: 'precipitation_mm', type: 'number', minimum: 0),
                    new OA\Property(property: 'wind_speed_kmh', type: 'number', minimum: 0),
                    new OA\Property(property: 'weather_code', type: 'integer'),
                    new OA\Property(property: 'condition', type: 'string'),
                    new OA\Property(property: 'observed_at', type: 'string', description: 'Observation time in Europe/Sarajevo local time.'),
                ]),
                new OA\Property(property: 'dining_tip', type: 'string'),
                new OA\Property(property: 'provider', type: 'string', example: 'Open-Meteo'),
            ]),
            new OA\Schema(schema: 'UserEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ]),
            new OA\Schema(schema: 'UserCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
            ]),
            new OA\Schema(schema: 'RestaurantEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Restaurant'),
            ]),
            new OA\Schema(schema: 'RestaurantCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Restaurant')),
            ]),
            new OA\Schema(schema: 'MenuItemEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/MenuItem'),
            ]),
            new OA\Schema(schema: 'MenuItemCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MenuItem')),
            ]),
            new OA\Schema(schema: 'OrderEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Order'),
            ]),
            new OA\Schema(schema: 'OrderCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Order')),
            ]),
            new OA\Schema(schema: 'OrderItemEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/OrderItem'),
            ]),
            new OA\Schema(schema: 'OrderItemCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItem')),
            ]),
            new OA\Schema(schema: 'ReviewEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
            ]),
            new OA\Schema(schema: 'ReviewCollection', type: 'object', properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
            ]),
            new OA\Schema(schema: 'WeatherEnvelope', type: 'object', properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Weather'),
            ]),
        ],
        responses: [
            new OA\Response(response: 'BadRequest', description: 'The request body or parameters are invalid.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorEnvelope')),
            new OA\Response(response: 'NotFound', description: 'The requested record or endpoint does not exist.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorEnvelope')),
            new OA\Response(response: 'ServerError', description: 'The request could not be completed.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorEnvelope')),
            new OA\Response(response: 'ServiceUnavailable', description: 'Sarajevo weather data is temporarily unavailable.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorEnvelope')),
            new OA\Response(response: 'Created', description: 'The record was created.', content: new OA\JsonContent(ref: '#/components/schemas/CreatedEnvelope')),
            new OA\Response(response: 'Deleted', description: 'The record was deleted.', content: new OA\JsonContent(ref: '#/components/schemas/DeletedEnvelope')),
        ],
        requestBodies: [
            new OA\RequestBody(request: 'CreateUser', description: 'Customer account fields. The service hashes the password and assigns the customer role.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateUser')),
            new OA\RequestBody(request: 'UpdateUser', description: 'User profile fields; role and password_hash are not writable.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUser')),
            new OA\RequestBody(request: 'CreateRestaurant', description: 'New restaurant fields.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateRestaurant')),
            new OA\RequestBody(request: 'UpdateRestaurant', description: 'Restaurant fields to update.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateRestaurant')),
            new OA\RequestBody(request: 'CreateMenuItem', description: 'New menu item fields.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateMenuItem')),
            new OA\RequestBody(request: 'UpdateMenuItem', description: 'Menu item fields to update.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateMenuItem')),
            new OA\RequestBody(request: 'CreateOrder', description: 'New order fields.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateOrder')),
            new OA\RequestBody(request: 'UpdateOrder', description: 'Order fields to update.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateOrder')),
            new OA\RequestBody(request: 'CreateOrderItem', description: 'New order line fields.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateOrderItem')),
            new OA\RequestBody(request: 'UpdateOrderItem', description: 'Order line fields to update.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateOrderItem')),
            new OA\RequestBody(request: 'CreateReview', description: 'Review fields; the user must have a delivered order at this restaurant and comments must be plain text.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateReview')),
            new OA\RequestBody(request: 'UpdateReview', description: 'Review fields to update; comments must be plain text and the review must remain eligible.', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateReview')),
        ]
    )
)]
final class ApiDocumentationSpec
{
}
