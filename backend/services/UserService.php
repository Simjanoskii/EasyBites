<?php

declare(strict_types=1);

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/InputValidator.php';

final class UserService extends EntityService
{
    public function __construct(UserDAO $dao)
    {
        parent::__construct($dao);
    }

    protected function present(array $record): array
    {
        unset($record['password_hash']);

        return $record;
    }

    protected function validate(array $data, bool $creating): array
    {
        InputValidator::assertAllowedFields($data, ['full_name', 'email', 'password']);
        if ($creating) {
            foreach (['full_name', 'email', 'password'] as $required) {
                if (!array_key_exists($required, $data)) {
                    throw new InvalidArgumentException(sprintf('%s is required.', $required));
                }
            }
        }
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $record = [];
        if (array_key_exists('full_name', $data)) {
            $record['full_name'] = InputValidator::text($data['full_name'], 'full_name', 120);
        }
        if (array_key_exists('email', $data)) {
            $email = InputValidator::text($data['email'], 'email', 254);
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidArgumentException('email must be a valid email address.');
            }
            $record['email'] = strtolower($email);
        }
        if (array_key_exists('password', $data)) {
            $password = $data['password'];
            if (!is_string($password) || strlen($password) < 8 || strlen($password) > 128) {
                throw new InvalidArgumentException('password must be between 8 and 128 characters.');
            }
            $record['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ($creating) {
            $record['role'] = 'customer';
        }

        return $record;
    }
}
