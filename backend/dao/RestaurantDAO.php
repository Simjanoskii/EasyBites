<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

final class RestaurantDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'restaurants', 'id', [
            'name',
            'description',
            'address',
            'phone',
            'is_active',
        ]);
    }
}
