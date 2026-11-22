<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class RestaurantService extends EntityService
{
    public function __construct(RestaurantDAO $dao)
    {
        parent::__construct($dao);
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['name', 'description', 'address', 'phone', 'is_active']);
        if ($creating) {
            foreach (['name', 'address'] as $required) {
                if (!array_key_exists($required, $data)) {
                    throw new InvalidArgumentException(sprintf('%s is required.', $required));
                }
            }
        }
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $record = [];
        foreach (['name' => 150, 'address' => 255] as $field => $maxLength) {
            if (array_key_exists($field, $data)) {
                $record[$field] = InputValidator::text($data[$field], $field, $maxLength);
            }
        }
        foreach (['description' => 16000, 'phone' => 32] as $field => $maxLength) {
            if (array_key_exists($field, $data)) {
                $record[$field] = InputValidator::text($data[$field], $field, $maxLength, false);
            } elseif ($creating) {
                $record[$field] = null;
            }
        }
        if (array_key_exists('is_active', $data)) {
            $record['is_active'] = InputValidator::boolean($data['is_active'], 'is_active');
        } elseif ($creating) {
            $record['is_active'] = 1;
        }

        return $record;
    }
}
