<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

$databasePath = tempnam(sys_get_temp_dir(), 'easybites-m3-db-');
$serverLog = tempnam(sys_get_temp_dir(), 'easybites-m3-server-');
$logPath = sys_get_temp_dir() . '/easybites-m3-logs-' . getmypid();
if ($databasePath === false || $serverLog === false || !mkdir($logPath)) {
    throw new RuntimeException('Could not create temporary test files.');
}

$pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(
    "CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'customer',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE restaurants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT NULL,
        address TEXT NOT NULL,
        phone TEXT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE menu_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE CASCADE,
        name TEXT NOT NULL,
        description TEXT NULL,
        category TEXT NOT NULL,
        price NUMERIC NOT NULL CHECK (price >= 0),
        is_available INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
        restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE RESTRICT,
        status TEXT NOT NULL DEFAULT 'pending',
        total NUMERIC NOT NULL DEFAULT 0 CHECK (total >= 0),
        delivery_address TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
        menu_item_id INTEGER NOT NULL REFERENCES menu_items(id) ON DELETE RESTRICT,
        quantity INTEGER NOT NULL CHECK (quantity > 0),
        unit_price NUMERIC NOT NULL CHECK (unit_price >= 0),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (order_id, menu_item_id)
    );
    CREATE TABLE reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE CASCADE,
        rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (user_id, restaurant_id)
    );"
);
$pdo = null;

putenv('EASYBITES_TEST_DB_PATH=' . $databasePath);
putenv('EASYBITES_TEST_LOG_PATH=' . $logPath);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if ($socket === false) {
    throw new RuntimeException('Could not reserve a local test port.');
}
$address = stream_socket_get_name($socket, false);
fclose($socket);
$port = (int) substr(strrchr($address, ':'), 1);
$command = [PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/http-router.php'];
$descriptors = [
    0 => ['file', '/dev/null', 'r'],
    1 => ['file', $serverLog, 'a'],
    2 => ['file', $serverLog, 'a'],
];
$server = proc_open($command, $descriptors, $pipes, __DIR__);
if (!is_resource($server)) {
    throw new RuntimeException('Could not start the local test server.');
}

$failures = 0;
$checks = 0;
$baseUrl = 'http://127.0.0.1:' . $port;

function check(bool $condition, string $label): void
{
    global $checks, $failures;
    $checks++;
    if (!$condition) {
        $failures++;
        fwrite(STDERR, "FAIL: {$label}\n");
        return;
    }
    echo "PASS: {$label}\n";
}

function request(
    string $method,
    string $path,
    ?string $body = null,
    string $origin = 'http://127.0.0.1:5500'
): array
{
    global $baseUrl;
    $headers = ['Accept: application/json', 'Origin: ' . $origin];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body ?? '',
            'ignore_errors' => true,
            'timeout' => 3,
        ],
    ]);
    $responseBody = @file_get_contents($baseUrl . $path, false, $context);
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s([0-9]{3})\s/', $statusLine, $matches);

    $result = [
        'status' => isset($matches[1]) ? (int) $matches[1] : 0,
        'body' => is_string($responseBody) ? $responseBody : '',
        'headers' => $http_response_header ?? [],
    ];
    if (getenv('EASYBITES_TEST_VERBOSE') === '1' && ($result['status'] >= 400 || $method === 'OPTIONS')) {
        fwrite(STDERR, sprintf("%s %s -> %d %s\n", $method, $path, $result['status'], $result['body']));
    }

    return $result;
}

function jsonBody(array $data): string
{
    return json_encode($data, JSON_THROW_ON_ERROR);
}

try {
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $probe = request('GET', '/api/v1/restaurants');
        if ($probe['status'] === 200) {
            $ready = true;
            break;
        }
        usleep(100000);
    }
    check($ready, 'FlightPHP test server starts and serves the restaurant route');

    $openApiResponse = request('GET', '/openapi.json');
    $openApi = json_decode($openApiResponse['body'], true);
    check($openApiResponse['status'] === 200 && is_array($openApi) && ($openApi['openapi'] ?? null) === '3.0.3', 'OpenAPI document is served as valid JSON');
    check(($openApi['servers'][0]['url'] ?? null) === $baseUrl, 'OpenAPI server URL matches the current request host');
    check(request('GET', '/docs')['status'] === 200 && strpos(request('GET', '/docs')['body'], 'SwaggerUIBundle') !== false, 'Swagger UI page is served');
    check(request('GET', '/docs/swagger.php')['status'] === 200, 'Swagger UI document endpoint is served');
    $preflight = request('OPTIONS', '/api/v1/restaurants');
    check(
        $preflight['status'] === 204
            && in_array('Access-Control-Allow-Origin: http://127.0.0.1:5500', $preflight['headers'], true)
            && in_array('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS', $preflight['headers'], true)
            && in_array('Access-Control-Allow-Headers: Content-Type', $preflight['headers'], true),
        'API preflight allows the configured local frontend'
    );
    $disallowedOrigin = request('OPTIONS', '/api/v1/restaurants', null, 'https://untrusted.example');
    check(
        $disallowedOrigin['status'] === 204
            && count(array_filter(
                $disallowedOrigin['headers'],
                static function (string $header): bool {
                    return stripos($header, 'Access-Control-Allow-Origin:') === 0;
                }
            )) === 0,
        'API preflight does not allow an unconfigured origin'
    );

    $restaurantResponse = request('POST', '/api/v1/restaurants', jsonBody([
        'name' => 'Test Restaurant',
        'address' => 'Baščaršija, Sarajevo',
    ]));
    check($restaurantResponse['status'] === 201, 'Restaurant create returns 201');
    $restaurantId = json_decode($restaurantResponse['body'], true)['data']['id'] ?? 0;
    check($restaurantId > 0, 'Restaurant create returns a record ID');
    check(request('GET', '/api/v1/restaurants')['status'] === 200, 'Restaurant list returns 200');
    check(request('GET', '/api/v1/restaurants/' . $restaurantId)['status'] === 200, 'Restaurant read returns 200');
    check(request('PUT', '/api/v1/restaurants/' . $restaurantId, jsonBody(['name' => 'Updated Restaurant']))['status'] === 200, 'Restaurant update returns 200');

    $userResponse = request('POST', '/api/v1/users', jsonBody([
        'full_name' => 'Test Customer',
        'email' => 'customer@example.test',
        'password' => 'test-password-123',
    ]));
    check($userResponse['status'] === 201, 'User create returns 201');
    $userId = json_decode($userResponse['body'], true)['data']['id'] ?? 0;
    $userRead = request('GET', '/api/v1/users/' . $userId);
    check($userRead['status'] === 200 && strpos($userRead['body'], 'password_hash') === false, 'User read omits password hashes');
    check(request('GET', '/api/v1/users')['status'] === 200, 'User list returns 200');
    check(request('PUT', '/api/v1/users/' . $userId, jsonBody(['full_name' => 'Updated Customer']))['status'] === 200, 'User update returns 200');
    check(request('PUT', '/api/v1/users/' . $userId, jsonBody(['role' => 'admin']))['status'] === 400, 'User role cannot be changed through the M3 API');
    $storedHash = $pdo = new PDO('sqlite:' . $databasePath);
    $passwordHash = $storedHash->query('SELECT password_hash FROM users WHERE id = ' . (int) $userId)->fetchColumn();
    check(is_string($passwordHash) && password_verify('test-password-123', $passwordHash), 'User password is hashed before persistence');
    $storedHash = null;

    $menuResponse = request('POST', '/api/v1/menu-items', jsonBody([
        'restaurant_id' => $restaurantId,
        'name' => 'Ćevapi',
        'category' => 'Local',
        'price' => 12.5,
    ]));
    check($menuResponse['status'] === 201, 'Menu item create returns 201');
    $menuId = json_decode($menuResponse['body'], true)['data']['id'] ?? 0;
    check(request('GET', '/api/v1/menu-items')['status'] === 200, 'Menu item list returns 200');
    check(request('GET', '/api/v1/menu-items/' . $menuId)['status'] === 200, 'Menu item read returns 200');
    check(request('PUT', '/api/v1/menu-items/' . $menuId, jsonBody(['price' => 13]))['status'] === 200, 'Menu item update returns 200');
    check(request('POST', '/api/v1/menu-items', jsonBody(['restaurant_id' => 99999, 'name' => 'Invalid', 'category' => 'Local', 'price' => 1]))['status'] === 400, 'Menu item rejects a missing restaurant reference');
    check(request('POST', '/api/v1/menu-items', jsonBody(['restaurant_id' => $restaurantId, 'name' => 'Invalid', 'category' => 'Local', 'price' => -1]))['status'] === 400, 'Menu item rejects a negative price');

    $orderResponse = request('POST', '/api/v1/orders', jsonBody([
        'user_id' => $userId,
        'restaurant_id' => $restaurantId,
        'delivery_address' => 'Sarajevo',
    ]));
    check($orderResponse['status'] === 201, 'Order create returns 201');
    $orderId = json_decode($orderResponse['body'], true)['data']['id'] ?? 0;
    check(request('GET', '/api/v1/orders')['status'] === 200, 'Order list returns 200');
    check(request('GET', '/api/v1/orders/' . $orderId)['status'] === 200, 'Order read returns 200');
    check(request('PUT', '/api/v1/orders/' . $orderId, jsonBody(['status' => 'preparing']))['status'] === 200, 'Order update returns 200');
    check(request('PUT', '/api/v1/orders/' . $orderId, jsonBody(['status' => 'unknown']))['status'] === 400, 'Order rejects unsupported statuses');

    $orderItemResponse = request('POST', '/api/v1/order-items', jsonBody([
        'order_id' => $orderId,
        'menu_item_id' => $menuId,
        'quantity' => 2,
        'unit_price' => 13,
    ]));
    check($orderItemResponse['status'] === 201, 'Order item create returns 201');
    $orderItemId = json_decode($orderItemResponse['body'], true)['data']['id'] ?? 0;
    check(request('GET', '/api/v1/order-items')['status'] === 200, 'Order item list returns 200');
    check(request('GET', '/api/v1/order-items/' . $orderItemId)['status'] === 200, 'Order item read returns 200');
    check(request('PUT', '/api/v1/order-items/' . $orderItemId, jsonBody(['quantity' => 3]))['status'] === 200, 'Order item update returns 200');
    check(request('PUT', '/api/v1/order-items/' . $orderItemId, jsonBody(['quantity' => 0]))['status'] === 400, 'Order item rejects a non-positive quantity');

    check(request('POST', '/api/v1/reviews', jsonBody([
        'user_id' => $userId,
        'restaurant_id' => $restaurantId,
        'rating' => 5,
    ]))['status'] === 400, 'Review requires a delivered order from the restaurant');
    check(request('PUT', '/api/v1/orders/' . $orderId, jsonBody(['status' => 'out_for_delivery']))['status'] === 200, 'Order can transition to out for delivery');
    check(request('PUT', '/api/v1/orders/' . $orderId, jsonBody(['status' => 'delivered']))['status'] === 200, 'Order can transition to delivered');
    check(request('POST', '/api/v1/reviews', jsonBody([
        'user_id' => $userId,
        'restaurant_id' => $restaurantId,
        'rating' => 5,
        'comment' => '<b>HTML is not allowed</b>',
    ]))['status'] === 400, 'Review rejects HTML markup');

    $reviewResponse = request('POST', '/api/v1/reviews', jsonBody([
        'user_id' => $userId,
        'restaurant_id' => $restaurantId,
        'rating' => 5,
        'comment' => 'Very good',
    ]));
    check($reviewResponse['status'] === 201, 'Review create returns 201');
    $reviewId = json_decode($reviewResponse['body'], true)['data']['id'] ?? 0;
    check(request('GET', '/api/v1/reviews')['status'] === 200, 'Review list returns 200');
    check(request('GET', '/api/v1/reviews/' . $reviewId)['status'] === 200, 'Review read returns 200');
    check(request('PUT', '/api/v1/reviews/' . $reviewId, jsonBody(['rating' => 4]))['status'] === 200, 'Review update returns 200');
    check(request('PUT', '/api/v1/reviews/' . $reviewId, jsonBody(['rating' => 6]))['status'] === 400, 'Review rejects a rating outside 1–5');
    check(request('PUT', '/api/v1/reviews/' . $reviewId, jsonBody(['comment' => '<script>alert(1)</script>']))['status'] === 400, 'Review update rejects HTML markup');
    check(request('POST', '/api/v1/reviews', jsonBody([
        'user_id' => $userId,
        'restaurant_id' => $restaurantId,
        'rating' => 5,
    ]))['status'] === 400, 'Review uniqueness constraint returns a client error');

    check(request('POST', '/api/v1/restaurants', '{"name":')['status'] === 400, 'Malformed JSON returns 400');
    check(request('POST', '/api/v1/restaurants', jsonBody(['name' => 'Missing address']))['status'] === 400, 'Missing required data returns 400');
    check(request('POST', '/api/v1/restaurants', jsonBody(['name' => 'Unexpected', 'address' => 'Sarajevo', 'id' => 9]))['status'] === 400, 'Unsupported fields return 400');
    check(request('GET', '/api/v1/restaurants/0')['status'] === 400, 'Invalid record IDs return 400');
    $nonNumericId = request('GET', '/api/v1/restaurants/not-a-number');
    check($nonNumericId['status'] === 404 && is_array(json_decode($nonNumericId['body'], true)), 'Nonnumeric record IDs use the global JSON 404 handler');
    $unknownEndpoint = request('GET', '/api/v1/not-a-resource');
    check($unknownEndpoint['status'] === 404 && is_array(json_decode($unknownEndpoint['body'], true)), 'Unknown endpoints use the global JSON 404 handler');
    $unsupportedMethod = request('PATCH', '/api/v1/restaurants');
    $allowHeaders = array_values(array_filter(
        $unsupportedMethod['headers'],
        static function (string $header): bool {
            return stripos($header, 'Allow:') === 0;
        }
    ));
    check(
        $unsupportedMethod['status'] === 405
            && is_array(json_decode($unsupportedMethod['body'], true))
            && count($allowHeaders) === 1
            && (strpos($allowHeaders[0], 'GET') !== false || strpos($allowHeaders[0], 'POST') !== false),
        'Unsupported methods return a JSON 405 with the allowed methods'
    );
    check(request('GET', '/api/v1/restaurants/999999')['status'] === 404, 'Missing records return 404');
    check(request('GET', '/api/v1/restaurants/' . PHP_INT_MAX . '0')['status'] === 400, 'Overflowing record IDs return 400');

    $weather = request('GET', '/api/v1/weather/sarajevo');
    $weatherData = json_decode($weather['body'], true)['data'] ?? [];
    check($weather['status'] === 200 && isset($weatherData['current']['temperature_c'], $weatherData['dining_tip']), 'Weather route returns normalized conditions and a dining tip');
    check(in_array('Access-Control-Allow-Origin: http://127.0.0.1:5500', $weather['headers'], true), 'Weather response allows the configured SPA origin');
    $weatherFailure = request('GET', '/api/v1/weather/sarajevo?mode=fail');
    check($weatherFailure['status'] === 503 && strpos($weatherFailure['body'], 'temporarily unavailable') !== false, 'Weather outage returns a user-friendly 503');
    check(request('GET', '/api/v1/weather/sarajevo?mode=malformed')['status'] === 503, 'Malformed provider data returns 503');
    check(request('GET', '/api/v1/weather/sarajevo?mode=invalid-code')['status'] === 503, 'Invalid weather codes return 503');
    check(request('GET', '/api/v1/weather/sarajevo?mode=timeout')['status'] === 503, 'Provider timeout returns 503');
    check(is_file($logPath . '/info.log') && is_file($logPath . '/error.log'), 'Weather success and failures are logged');

    foreach ([
        ['/api/v1/order-items/' . $orderItemId, 'order item'],
        ['/api/v1/reviews/' . $reviewId, 'review'],
        ['/api/v1/orders/' . $orderId, 'order'],
        ['/api/v1/menu-items/' . $menuId, 'menu item'],
        ['/api/v1/users/' . $userId, 'user'],
        ['/api/v1/restaurants/' . $restaurantId, 'restaurant'],
    ] as [$path, $name]) {
        check(request('DELETE', $path)['status'] === 200, ucfirst($name) . ' delete returns 200');
        check(request('GET', $path)['status'] === 404, ucfirst($name) . ' returns 404 after deletion');
    }
    check(request('PUT', '/api/v1/restaurants/' . $restaurantId, jsonBody(['name' => 'Missing']))['status'] === 404, 'Updating a missing record returns 404');
    check(request('DELETE', '/api/v1/restaurants/' . $restaurantId)['status'] === 404, 'Deleting a missing record returns 404');

    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->exec('DROP TABLE users');
    $pdo = null;
    $databaseFailure = request('GET', '/api/v1/users');
    check($databaseFailure['status'] === 500, 'Database failure returns 500');
    check(strpos($databaseFailure['body'], 'SQL') === false && strpos($databaseFailure['body'], $databasePath) === false, 'Database error response does not expose internals');

    $specPaths = $openApi['paths'] ?? [];
    $expectedOperations = [
        '/api/v1/users' => ['get', 'post'],
        '/api/v1/users/{id}' => ['get', 'put', 'delete'],
        '/api/v1/restaurants' => ['get', 'post'],
        '/api/v1/restaurants/{id}' => ['get', 'put', 'delete'],
        '/api/v1/menu-items' => ['get', 'post'],
        '/api/v1/menu-items/{id}' => ['get', 'put', 'delete'],
        '/api/v1/orders' => ['get', 'post'],
        '/api/v1/orders/{id}' => ['get', 'put', 'delete'],
        '/api/v1/order-items' => ['get', 'post'],
        '/api/v1/order-items/{id}' => ['get', 'put', 'delete'],
        '/api/v1/reviews' => ['get', 'post'],
        '/api/v1/reviews/{id}' => ['get', 'put', 'delete'],
        '/api/v1/weather/sarajevo' => ['get'],
        '/openapi.json' => ['get'],
        '/docs/swagger.php' => ['get'],
        '/docs' => ['get'],
    ];
    $coverage = true;
    foreach ($expectedOperations as $path => $methods) {
        foreach ($methods as $method) {
            $coverage = $coverage && isset($specPaths[$path][$method]);
        }
    }
    check($coverage, 'OpenAPI documents every CRUD, weather, and documentation route');
    $references = [];
    $collectReferences = static function ($value) use (&$collectReferences, &$references): void {
        if (!is_array($value)) {
            return;
        }
        if (isset($value['$ref']) && is_string($value['$ref'])) {
            $references[] = $value['$ref'];
        }
        foreach ($value as $nested) {
            $collectReferences($nested);
        }
    };
    $collectReferences($openApi);
    $resolvedReferences = true;
    foreach ($references as $reference) {
        if (strpos($reference, '#/') !== 0) {
            $resolvedReferences = false;
            break;
        }
        $target = $openApi;
        foreach (explode('/', substr($reference, 2)) as $segment) {
            $segment = str_replace(['~1', '~0'], ['/', '~'], $segment);
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                $resolvedReferences = false;
                break 2;
            }
            $target = $target[$segment];
        }
    }
    check($resolvedReferences, 'Every OpenAPI component reference resolves');
} finally {
    if ($failures > 0 && is_file($serverLog)) {
        fwrite(STDERR, "Test server output:\n" . file_get_contents($serverLog) . "\n");
    }
    proc_terminate($server);
    proc_close($server);
    putenv('EASYBITES_TEST_DB_PATH');
    putenv('EASYBITES_TEST_LOG_PATH');
    foreach ([$databasePath, $serverLog, $logPath . '/info.log', $logPath . '/error.log'] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (is_dir($logPath)) {
        rmdir($logPath);
    }
}

echo sprintf("\n%d checks, %d failures.\n", $checks, $failures);
exit($failures === 0 ? 0 : 1);
