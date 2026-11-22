<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class ReviewService extends EntityService
{
    private ReviewDAO $reviews;
    private UserDAO $users;
    private RestaurantDAO $restaurants;
    private OrderDAO $orders;

    public function __construct(ReviewDAO $dao, UserDAO $users, RestaurantDAO $restaurants, OrderDAO $orders)
    {
        parent::__construct($dao);
        $this->reviews = $dao;
        $this->users = $users;
        $this->restaurants = $restaurants;
        $this->orders = $orders;
    }

    public function update(int $id, array $data): void
    {
        $this->assertValidId($id);
        $existing = $this->reviews->findById($id);
        if ($existing === null) {
            throw new OutOfBoundsException('The requested record was not found.');
        }

        $record = $this->validate($data, false);
        $candidate = array_merge($existing, $record);
        $this->assertEligibleReview((int) $candidate['user_id'], (int) $candidate['restaurant_id'], $id);
        if (!$this->reviews->update($id, $record)) {
            throw new OutOfBoundsException('The requested record was not found.');
        }
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['user_id', 'restaurant_id', 'rating', 'comment']);
        if ($creating) {
            foreach (['user_id', 'restaurant_id', 'rating'] as $required) {
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
        if (array_key_exists('rating', $data)) {
            $record['rating'] = InputValidator::integer($data['rating'], 'rating', 1, 5);
        }
        if (array_key_exists('comment', $data)) {
            $comment = InputValidator::text($data['comment'], 'comment', 16000, false);
            if ($comment !== null && preg_match('/<\s*\/?\s*[A-Za-z][^>]*>/u', $comment) === 1) {
                throw new InvalidArgumentException('comment must be plain text without HTML tags.');
            }
            $record['comment'] = $comment;
        } elseif ($creating) {
            $record['comment'] = null;
        }

        if ($creating) {
            $this->assertEligibleReview($record['user_id'], $record['restaurant_id']);
        }

        return $record;
    }

    private function assertEligibleReview(int $userId, int $restaurantId, ?int $exceptReviewId = null): void
    {
        if (!$this->orders->hasDeliveredOrderFor($userId, $restaurantId)) {
            throw new InvalidArgumentException('A review can only be submitted after a delivered order from this restaurant.');
        }
        if ($this->reviews->existsForUserAndRestaurant($userId, $restaurantId, $exceptReviewId)) {
            throw new InvalidArgumentException('A user can submit only one review per restaurant.');
        }
    }
}
