<?php

declare(strict_types=1);

final class Database
{
    public static function getConnection(): PDO
    {
        $configPath = __DIR__ . '/config.php';
        if (!is_file($configPath)) {
            throw new RuntimeException(
                'Database configuration is missing. Copy config.example.php to config.php and set local credentials.'
            );
        }

        $config = require $configPath;
        if (!is_array($config) || !isset($config['database']) || !is_array($config['database'])) {
            throw new RuntimeException('Database configuration has an invalid structure.');
        }

        $database = $config['database'];
        foreach (['host', 'port', 'database', 'username', 'password', 'charset'] as $key) {
            if (!array_key_exists($key, $database) || !is_scalar($database[$key])) {
                throw new RuntimeException('Database configuration is missing a required setting.');
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $database['host'],
            (int) $database['port'],
            $database['database'],
            $database['charset']
        );

        return new PDO($dsn, (string) $database['username'], (string) $database['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
