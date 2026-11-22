<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

$_SERVER['SCRIPT_NAME'] = '/index.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:5500',
    'http://127.0.0.1:5500',
    'http://localhost:8080',
    'http://127.0.0.1:8080',
];
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$databasePath = getenv('EASYBITES_TEST_DB_PATH');
$logPath = getenv('EASYBITES_TEST_LOG_PATH');
if ($databasePath === false || $logPath === false) {
    http_response_code(500);
    exit;
}

$pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON');
$logger = new ApiLogger($logPath);
$weatherRequest = static function (string $url, int $timeout): array {
    if ($timeout !== 5 || strpos($url, 'api.open-meteo.com') === false) {
        throw new RuntimeException('Unexpected weather request configuration.');
    }
    if (($_GET['mode'] ?? '') === 'fail') {
        return ['status' => 503, 'body' => ''];
    }
    if (($_GET['mode'] ?? '') === 'timeout') {
        throw new RuntimeException('Simulated upstream timeout.');
    }
    if (($_GET['mode'] ?? '') === 'malformed') {
        return ['status' => 200, 'body' => '{"current":{"temperature_2m":"not-a-number"}}'];
    }
    if (($_GET['mode'] ?? '') === 'invalid-code') {
        return [
            'status' => 200,
            'body' => json_encode([
                'current' => [
                    'time' => '2026-11-22T13:00',
                    'temperature_2m' => 11.4,
                    'apparent_temperature' => 9.2,
                    'relative_humidity_2m' => 62,
                    'precipitation' => 0,
                    'wind_speed_10m' => 8.4,
                    'weather_code' => 2.5,
                ],
            ]),
        ];
    }

    return [
        'status' => 200,
        'body' => json_encode([
            'current' => [
                'time' => '2026-11-22T13:00',
                'temperature_2m' => 11.4,
                'apparent_temperature' => 9.2,
                'relative_humidity_2m' => 62,
                'precipitation' => 0,
                'wind_speed_10m' => 8.4,
                'weather_code' => 2,
            ],
        ]),
    ];
};

ApiApplication::register($pdo, new WeatherService($weatherRequest, $logger), $logger);
Flight::start();
