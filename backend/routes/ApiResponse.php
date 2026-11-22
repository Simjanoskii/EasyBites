<?php

declare(strict_types=1);

final class ApiResponse
{
    public static function run(callable $action, ApiLogger $logger): void
    {
        try {
            $action();
        } catch (InvalidArgumentException $exception) {
            Flight::json(['error' => $exception->getMessage()], 400);
        } catch (OutOfBoundsException $exception) {
            Flight::json(['error' => 'The requested record was not found.'], 404);
        } catch (UpstreamApiException $exception) {
            Flight::json(['error' => 'Current Sarajevo weather is temporarily unavailable. Please try again later.'], 503);
        } catch (PDOException $exception) {
            $sqlState = isset($exception->errorInfo[0]) ? (string) $exception->errorInfo[0] : (string) $exception->getCode();
            $logger->error('Database request failed', ['sql_state' => $sqlState]);
            if (strncmp($sqlState, '23', 2) === 0) {
                Flight::json(['error' => 'The request conflicts with existing data or database rules.'], 400);
                return;
            }
            Flight::json(['error' => 'The request could not be completed. Please try again later.'], 500);
        } catch (Throwable $exception) {
            $logger->error('Unhandled API request failure', ['error_type' => get_class($exception)]);
            Flight::json(['error' => 'The request could not be completed. Please try again later.'], 500);
        }
    }
}
