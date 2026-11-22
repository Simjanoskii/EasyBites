<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class OrderService extends EntityService
{
    private UserDAO $users;
    private RestaurantDAO $restaurants;

    public function __construct(OrderDAO $dao, UserDAO $users, RestaurantDAO $restaurants)
    {
        parent::__construct($dao);
        $this->users = $users;
        $this->restaurants = $restaurants;
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['user_id', 'restaurant_id', 'status', 'total', 'delivery_address']);
        if ($creating) {
            foreach (['user_id', 'restaurant_id', 'delivery_address'] as $required) {
                if (!array_key_exists($required, $data)) {
                    throw new InvalidArgumentException(sprintf('%s is required.', $required));
                }
            }
        }
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $record = [];
        foreach (['user_id' => $this->users, 'restaurant_id' => $this->restaurants] as $field => $dao) {
            if (array_key_exists($field, $data)) {
                $id = InputValidator::integer($data[$field], $field);
                InputValidator::assertRecordExists($dao, $id, $field);
                $record[$field] = $id;
            }
        }
        if (array_key_exists('status', $data)) {
            $status = InputValidator::text($data['status'], 'status', 32);
            if (!in_array($status, ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'], true)) {
                throw new InvalidArgumentException('status is not a supported order status.');
            }
            $record['status'] = $status;
        } elseif ($creating) {
            $record['status'] = 'pending';
        }
        if (array_key_exists('total', $data)) {
            $record['total'] = InputValidator::money($data['total'], 'total');
        } elseif ($creating) {
            $record['total'] = '0.00';
        }
        if (array_key_exists('delivery_address', $data)) {
            $record['delivery_address'] = InputValidator::text($data['delivery_address'], 'delivery_address', 255);
        }

        return $record;
    }
}
