<?php

declare(strict_types=1);

final class ApiResponse
{
    public static function run(callable $action, ApiLogger $logger): void
    {
        try {
            $action();
        } catch (Throwable $exception) {
            ApiErrorHandler::handle($exception, $logger);
        }
    }
}
