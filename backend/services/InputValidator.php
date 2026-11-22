<?php

declare(strict_types=1);

final class InputValidator
{
    public static function assertAllowedFields(array $data, array $allowed): void
    {
        foreach (array_keys($data) as $field) {
            if (!is_string($field) || !in_array($field, $allowed, true)) {
                throw new InvalidArgumentException('The request contains an unsupported field.');
            }
        }
    }

    public static function text($value, string $field, int $maxLength, bool $required = true): ?string
    {
        if ($value === null && !$required) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('%s must be text.', $field));
        }

        $value = trim($value);
        if ($value === '' && !$required) {
            return null;
        }
        $length = preg_match_all('/./us', $value);
        if ($value === '' || $length === false || $length > $maxLength) {
            throw new InvalidArgumentException(sprintf('%s is required and must be at most %d characters.', $field, $maxLength));
        }

        return $value;
    }

    public static function integer($value, string $field, int $minimum = 1, int $maximum = PHP_INT_MAX): int
    {
        if (is_int($value)) {
            $integer = $value;
        } elseif (is_string($value) && preg_match('/\A(?:0|[1-9][0-9]*)\z/', $value) === 1) {
            $validated = filter_var($value, FILTER_VALIDATE_INT);
            if ($validated === false) {
                throw new InvalidArgumentException(sprintf('%s must be a valid integer.', $field));
            }
            $integer = $validated;
        } else {
            throw new InvalidArgumentException(sprintf('%s must be a valid integer.', $field));
        }

        if ($integer < $minimum || $integer > $maximum) {
            throw new InvalidArgumentException(sprintf('%s must be between %d and %d.', $field, $minimum, $maximum));
        }

        return $integer;
    }

    public static function money($value, string $field): string
    {
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative amount with up to two decimals.', $field));
        }
        $amount = (string) $value;
        if (preg_match('/\A(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?\z/', $amount) !== 1 || (float) $amount > 99999999.99) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative amount with up to two decimals.', $field));
        }

        return number_format((float) $amount, 2, '.', '');
    }

    public static function boolean($value, string $field): int
    {
        if ($value === true || $value === 1 || $value === '1') {
            return 1;
        }
        if ($value === false || $value === 0 || $value === '0') {
            return 0;
        }

        throw new InvalidArgumentException(sprintf('%s must be true or false.', $field));
    }

    public static function assertRecordExists(BaseDAO $dao, int $id, string $field): void
    {
        if ($dao->findById($id) === null) {
            throw new InvalidArgumentException(sprintf('%s does not refer to an existing record.', $field));
        }
    }
}
