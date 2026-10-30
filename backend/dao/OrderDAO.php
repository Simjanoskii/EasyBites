<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

final class OrderDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'orders', 'id', [
            'user_id',
            'restaurant_id',
            'status',
            'total',
            'delivery_address',
        ]);
    }
}
