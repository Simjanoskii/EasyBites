<?php

declare(strict_types=1);

final class ApiRequest
{
    public static function jsonObject(): array
    {
        $body = Flight::request()->getBody();
        $data = json_decode($body);
        if (json_last_error() !== JSON_ERROR_NONE || !$data instanceof stdClass) {
            throw new InvalidArgumentException('Request body must be a valid JSON object.');
        }

        return get_object_vars($data);
    }

    public static function positiveId($value): int
    {
        if (!is_string($value) || preg_match('/\A[1-9][0-9]*\z/', $value) !== 1) {
            throw new InvalidArgumentException('Record ID must be a positive integer.');
        }
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false) {
            throw new InvalidArgumentException('Record ID must be a positive integer.');
        }

        return $id;
    }
}
