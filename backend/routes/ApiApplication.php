<?php

declare(strict_types=1);

final class ApiApplication
{
    public static function register(PDO $pdo, ?WeatherService $weather = null, ?ApiLogger $logger = null): void
    {
        $logger = $logger ?? new ApiLogger();
        Flight::map('error', static function (Throwable $exception) use ($logger): void {
            ApiErrorHandler::handle($exception, $logger);
        });
        Flight::map('notFound', static function (): void {
            ApiErrorHandler::notFound();
        });
        Flight::map('methodNotFound', static function ($route): void {
            $methods = is_object($route) && isset($route->methods) && is_array($route->methods)
                ? $route->methods
                : [];
            Flight::response()->header('Allow', implode(', ', $methods));
            Flight::json(['error' => 'The requested method is not allowed.'], 405);
        });

        $users = new UserDAO($pdo);
        $restaurants = new RestaurantDAO($pdo);
        $menuItems = new MenuItemDAO($pdo);
        $orders = new OrderDAO($pdo);
        $orderItems = new OrderItemDAO($pdo);
        $reviews = new ReviewDAO($pdo);

        UserRoutes::register(new UserService($users), $logger);
        RestaurantRoutes::register(new RestaurantService($restaurants), $logger);
        MenuItemRoutes::register(new MenuItemService($menuItems, $restaurants), $logger);
        OrderRoutes::register(new OrderService($orders, $users, $restaurants), $logger);
        OrderItemRoutes::register(new OrderItemService($orderItems, $orders, $menuItems), $logger);
        ReviewRoutes::register(new ReviewService($reviews, $users, $restaurants, $orders), $logger);
        WeatherRoutes::register($weather ?? new WeatherService(null, $logger), $logger);
        DocumentationRoutes::register($logger);
    }
}
