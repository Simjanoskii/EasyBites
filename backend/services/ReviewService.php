<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class ReviewService extends EntityService
{
    private UserDAO $users;
    private RestaurantDAO $restaurants;

    public function __construct(ReviewDAO $dao, UserDAO $users, RestaurantDAO $restaurants)
    {
        parent::__construct($dao);
        $this->users = $users;
        $this->restaurants = $restaurants;
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
            $record['comment'] = InputValidator::text($data['comment'], 'comment', 16000, false);
        } elseif ($creating) {
            $record['comment'] = null;
        }

        return $record;
    }
}
