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

    public function hasDeliveredOrderFor(int $userId, int $restaurantId): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT 1 FROM orders
             WHERE user_id = :user_id AND restaurant_id = :restaurant_id AND status = 'delivered'
             LIMIT 1"
        );
        $statement->execute([
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
        ]);

        return $statement->fetchColumn() !== false;
    }
}
