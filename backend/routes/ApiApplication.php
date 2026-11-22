<?php

declare(strict_types=1);

final class ApiApplication
{
    public static function register(PDO $pdo, ?WeatherService $weather = null, ?ApiLogger $logger = null): void
    {
        $logger = $logger ?? new ApiLogger();
        $users = new UserDAO($pdo);
        $restaurants = new RestaurantDAO($pdo);
        $menuItems = new MenuItemDAO($pdo);
        $orders = new OrderDAO($pdo);
        $orderItems = new OrderItemDAO($pdo);
        $reviews = new ReviewDAO($pdo);

        $services = [
            'users' => new UserService($users),
            'restaurants' => new RestaurantService($restaurants),
            'menu-items' => new MenuItemService($menuItems, $restaurants),
            'orders' => new OrderService($orders, $users, $restaurants),
            'order-items' => new OrderItemService($orderItems, $orders, $menuItems),
            'reviews' => new ReviewService($reviews, $users, $restaurants),
        ];

        foreach ($services as $path => $service) {
            self::registerCrudRoutes($path, $service, $logger);
        }

        $weather = $weather ?? new WeatherService(null, $logger);
        Flight::route('GET /api/v1/weather/sarajevo', function () use ($weather, $logger): void {
            ApiResponse::run(function () use ($weather): void {
                Flight::json(['data' => $weather->getSarajevoConditions()], 200);
            }, $logger);
        });

        Flight::route('OPTIONS /api/v1/*', static function (): void {
            Flight::response()->status(204);
        });

        Flight::route('GET /openapi.json', static function (): void {
            $path = __DIR__ . '/../openapi.json';
            $document = file_get_contents($path);
            if ($document === false) {
                Flight::json(['error' => 'API documentation is unavailable.'], 500);
                return;
            }
            Flight::response()->header('Content-Type', 'application/json; charset=utf-8')->write($document);
        });

        Flight::route('GET /docs', static function (): void {
            $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>EasyBites API documentation</title><link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui.css"></head><body><div id="swagger-ui"></div><script src="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui-bundle.js"></script><script>window.onload = () => SwaggerUIBundle({ url: "/openapi.json", dom_id: "#swagger-ui" });</script></body></html>';
            Flight::response()->header('Content-Type', 'text/html; charset=utf-8')->write($html);
        });
    }

    private static function registerCrudRoutes(string $path, EntityService $service, ApiLogger $logger): void
    {
        Flight::route('GET /api/v1/' . $path, static function () use ($service, $logger): void {
            ApiResponse::run(static function () use ($service): void {
                Flight::json(['data' => $service->findAll()], 200);
            }, $logger);
        });

        Flight::route('GET /api/v1/' . $path . '/@id', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                Flight::json(['data' => $service->findById(ApiRequest::positiveId($id))], 200);
            }, $logger);
        });

        Flight::route('POST /api/v1/' . $path, static function () use ($path, $service, $logger): void {
            ApiResponse::run(static function () use ($path, $service): void {
                $id = $service->create(ApiRequest::jsonObject());
                Flight::response()->header('Location', '/api/v1/' . $path . '/' . $id);
                Flight::json(['data' => ['id' => $id]], 201);
            }, $logger);
        });

        Flight::route('PUT /api/v1/' . $path . '/@id', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                $id = ApiRequest::positiveId($id);
                $service->update($id, ApiRequest::jsonObject());
                Flight::json(['data' => $service->findById($id)], 200);
            }, $logger);
        });

        Flight::route('DELETE /api/v1/' . $path . '/@id', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                $service->delete(ApiRequest::positiveId($id));
                Flight::json(['message' => 'Record deleted.'], 200);
            }, $logger);
        });
    }
}
