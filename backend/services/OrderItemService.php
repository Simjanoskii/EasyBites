<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class OrderItemService extends EntityService
{
    private OrderDAO $orders;
    private MenuItemDAO $menuItems;

    public function __construct(OrderItemDAO $dao, OrderDAO $orders, MenuItemDAO $menuItems)
    {
        parent::__construct($dao);
        $this->orders = $orders;
        $this->menuItems = $menuItems;
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['order_id', 'menu_item_id', 'quantity', 'unit_price']);
        if ($creating) {
            foreach (['order_id', 'menu_item_id', 'quantity', 'unit_price'] as $required) {
                if (!array_key_exists($required, $data)) {
                    throw new InvalidArgumentException(sprintf('%s is required.', $required));
                }
            }
        }
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $record = [];
        foreach (['order_id' => $this->orders, 'menu_item_id' => $this->menuItems] as $field => $dao) {
            if (array_key_exists($field, $data)) {
                $id = InputValidator::integer($data[$field], $field);
                InputValidator::assertRecordExists($dao, $id, $field);
                $record[$field] = $id;
            }
        }
        if (array_key_exists('quantity', $data)) {
            $record['quantity'] = InputValidator::integer($data['quantity'], 'quantity', 1, 32767);
        }
        if (array_key_exists('unit_price', $data)) {
            $record['unit_price'] = InputValidator::money($data['unit_price'], 'unit_price');
        }

        return $record;
    }
}
