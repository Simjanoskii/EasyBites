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

    public function existsForUserAndRestaurant(int $userId, int $restaurantId, ?int $exceptReviewId = null): bool
    {
        $sql = 'SELECT 1 FROM reviews WHERE user_id = :user_id AND restaurant_id = :restaurant_id';
        $parameters = [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
        ];
        if ($exceptReviewId !== null) {
            $sql .= ' AND id <> :except_review_id';
            $parameters['except_review_id'] = $exceptReviewId;
        }
        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }
}
