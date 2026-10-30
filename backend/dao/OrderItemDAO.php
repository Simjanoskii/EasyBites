<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

final class OrderItemDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'order_items', 'id', [
            'order_id',
            'menu_item_id',
            'quantity',
            'unit_price',
        ]);
    }
}
