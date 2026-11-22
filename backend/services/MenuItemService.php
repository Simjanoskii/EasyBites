<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class MenuItemService extends EntityService
{
    private RestaurantDAO $restaurants;

    public function __construct(MenuItemDAO $dao, RestaurantDAO $restaurants)
    {
        parent::__construct($dao);
        $this->restaurants = $restaurants;
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['restaurant_id', 'name', 'description', 'category', 'price', 'is_available']);
        if ($creating) {
            foreach (['restaurant_id', 'name', 'category', 'price'] as $required) {
                if (!array_key_exists($required, $data)) {
                    throw new InvalidArgumentException(sprintf('%s is required.', $required));
                }
            }
        }
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $record = [];
        if (array_key_exists('restaurant_id', $data)) {
            $restaurantId = InputValidator::integer($data['restaurant_id'], 'restaurant_id');
            InputValidator::assertRecordExists($this->restaurants, $restaurantId, 'restaurant_id');
            $record['restaurant_id'] = $restaurantId;
        }
        foreach (['name' => 150, 'category' => 60] as $field => $maxLength) {
            if (array_key_exists($field, $data)) {
                $record[$field] = InputValidator::text($data[$field], $field, $maxLength);
            }
        }
        if (array_key_exists('description', $data)) {
            $record['description'] = InputValidator::text($data['description'], 'description', 16000, false);
        } elseif ($creating) {
            $record['description'] = null;
        }
        if (array_key_exists('price', $data)) {
            $record['price'] = InputValidator::money($data['price'], 'price');
        }
        if (array_key_exists('is_available', $data)) {
            $record['is_available'] = InputValidator::boolean($data['is_available'], 'is_available');
        } elseif ($creating) {
            $record['is_available'] = 1;
        }

        return $record;
    }
}
