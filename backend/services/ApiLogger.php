<?php

declare(strict_types=1);

final class ApiLogger
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? __DIR__ . '/../logs';
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('info.log', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error.log', $message, $context);
    }

    private function write(string $file, string $message, array $context): void
    {
        $record = [
            'time' => gmdate(DATE_ATOM),
            'message' => $message,
            'context' => $context,
        ];
        $json = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        $path = $this->directory . DIRECTORY_SEPARATOR . $file;
        if ($json !== false && is_dir($this->directory) && @file_put_contents($path, $json . PHP_EOL, FILE_APPEND | LOCK_EX) !== false) {
            return;
        }

        error_log($message . ': ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
