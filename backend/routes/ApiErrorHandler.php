<?php

declare(strict_types=1);

final class ApiErrorHandler
{
    public static function handle(Throwable $exception, ApiLogger $logger): void
    {
        if ($exception instanceof InvalidArgumentException) {
            Flight::json(['error' => $exception->getMessage()], 400);
            return;
        }

        if ($exception instanceof OutOfBoundsException) {
            Flight::json(['error' => 'The requested record was not found.'], 404);
            return;
        }

        if ($exception instanceof UpstreamApiException) {
            Flight::json(['error' => 'Current Sarajevo weather is temporarily unavailable. Please try again later.'], 503);
            return;
        }

        if ($exception instanceof PDOException) {
            $sqlState = isset($exception->errorInfo[0]) ? (string) $exception->errorInfo[0] : (string) $exception->getCode();
            $logger->error('Database request failed', ['sql_state' => $sqlState]);
            if (strncmp($sqlState, '23', 2) === 0) {
                Flight::json(['error' => 'The request conflicts with existing data or database rules.'], 400);
                return;
            }
        } else {
            $logger->error('Unhandled API request failure', ['error_type' => get_class($exception)]);
        }

        Flight::json(['error' => 'The request could not be completed. Please try again later.'], 500);
    }

    public static function notFound(): void
    {
        Flight::json(['error' => 'The requested endpoint was not found.'], 404);
    }
}
