<?php

declare(strict_types=1);

use OpenApi\Generator;

final class ApiDocumentation
{
    public static function generate(): string
    {
        if (!defined('API_BASE_URL')) {
            define('API_BASE_URL', self::baseUrl());
        }

        $sources = array_merge(
            [__DIR__ . '/../public/v1/docs/doc_setup.php'],
            glob(__DIR__ . '/../routes/*Routes.php') ?: []
        );
        foreach ($sources as $source) {
            require_once $source;
        }

        $openApi = (new Generator())->generate($sources);
        if ($openApi === null) {
            throw new RuntimeException('OpenAPI generation returned no document.');
        }

        return $openApi->toJson();
    }

    private static function baseUrl(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!is_string($host) || preg_match('/\A(?:[A-Za-z0-9.-]+|\[[0-9A-Fa-f:]+\])(?::[0-9]{1,5})?\z/', $host) !== 1) {
            throw new RuntimeException('The request host is not valid for API documentation.');
        }

        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/openapi.json', PHP_URL_PATH);
        if (!is_string($requestPath)) {
            throw new RuntimeException('The API documentation request path is invalid.');
        }

        $basePath = preg_replace(
            '~/(?:openapi\.json|docs(?:/swagger\.php)?|public/v1/docs/swagger\.php)$~',
            '',
            $requestPath
        );
        if (!is_string($basePath)) {
            throw new RuntimeException('The API documentation base path could not be determined.');
        }

        return $scheme . '://' . $host . rtrim($basePath, '/');
    }
}
