<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

final class ReviewDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'reviews', 'id', [
            'user_id',
            'restaurant_id',
            'rating',
            'comment',
        ]);
    }
}
