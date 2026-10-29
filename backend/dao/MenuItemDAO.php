<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

final class MenuItemDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'menu_items', 'id', [
            'restaurant_id',
            'name',
            'description',
            'category',
            'price',
            'is_available',
        ]);
    }
}
