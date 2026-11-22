<?php

declare(strict_types=1);

require_once __DIR__ . '/ApiLogger.php';
require_once __DIR__ . '/UpstreamApiException.php';

final class WeatherService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast?latitude=43.8563&longitude=18.4131&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m&timezone=Europe%2FSarajevo';

    private Closure $request;
    private ApiLogger $logger;
    private int $timeoutSeconds;

    public function __construct(?callable $request = null, ?ApiLogger $logger = null, int $timeoutSeconds = 5)
    {
        if ($timeoutSeconds < 1 || $timeoutSeconds > 15) {
            throw new InvalidArgumentException('Weather request timeout must be between 1 and 15 seconds.');
        }

        $this->request = Closure::fromCallable($request ?? [$this, 'requestJson']);
        $this->logger = $logger ?? new ApiLogger();
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function getSarajevoConditions(): array
    {
        $startedAt = microtime(true);
        try {
            $response = ($this->request)(self::API_URL, $this->timeoutSeconds);
            if (!is_array($response) || !isset($response['status'], $response['body']) || $response['status'] !== 200 || !is_string($response['body'])) {
                throw new RuntimeException('Weather provider returned an unsuccessful response.');
            }
            $payload = json_decode($response['body'], true);
            $current = is_array($payload) ? ($payload['current'] ?? null) : null;
            if (!is_array($current)) {
                throw new RuntimeException('Weather provider response did not contain current conditions.');
            }

            $weather = $this->validateCurrentConditions($current);
            $result = [
                'location' => [
                    'name' => 'Sarajevo',
                    'latitude' => 43.8563,
                    'longitude' => 18.4131,
                ],
                'current' => $weather,
                'dining_tip' => $this->diningTip($weather['temperature_c'], $weather['precipitation_mm'], $weather['weather_code']),
                'provider' => 'Open-Meteo',
            ];
            $this->logger->info('Sarajevo weather request completed', [
                'provider' => 'Open-Meteo',
                'status' => 200,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->logger->error('Sarajevo weather request failed', [
                'provider' => 'Open-Meteo',
                'error_type' => get_class($exception),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
            throw new UpstreamApiException('Current Sarajevo weather is temporarily unavailable.', 0, $exception);
        }
    }

    private function requestJson(string $url, int $timeoutSeconds): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: EasyBites/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $http_response_header = [];
        $body = @file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? '';
        if (!is_string($body) || preg_match('/\s([0-9]{3})\s/', $statusLine, $matches) !== 1) {
            throw new RuntimeException('Weather provider could not be reached.');
        }

        return [
            'status' => (int) $matches[1],
            'body' => $body,
        ];
    }

    private function validateCurrentConditions(array $current): array
    {
        $requiredNumeric = [
            'temperature_2m',
            'apparent_temperature',
            'relative_humidity_2m',
            'precipitation',
            'wind_speed_10m',
            'weather_code',
        ];
        foreach ($requiredNumeric as $key) {
            if (!isset($current[$key]) || !is_numeric($current[$key]) || !is_finite((float) $current[$key])) {
                throw new RuntimeException('Weather provider returned incomplete current conditions.');
            }
        }
        $weatherCode = (float) $current['weather_code'];
        if ($weatherCode < 0 || $weatherCode > 99 || floor($weatherCode) !== $weatherCode) {
            throw new RuntimeException('Weather provider returned an invalid weather code.');
        }
        if (!isset($current['time']) || !is_string($current['time']) || strtotime($current['time']) === false) {
            throw new RuntimeException('Weather provider returned an invalid observation time.');
        }
        if ((float) $current['relative_humidity_2m'] < 0 || (float) $current['relative_humidity_2m'] > 100 || (float) $current['precipitation'] < 0 || (float) $current['wind_speed_10m'] < 0) {
            throw new RuntimeException('Weather provider returned out-of-range current conditions.');
        }

        $code = (int) $current['weather_code'];

        return [
            'temperature_c' => round((float) $current['temperature_2m'], 1),
            'feels_like_c' => round((float) $current['apparent_temperature'], 1),
            'humidity_percent' => (int) $current['relative_humidity_2m'],
            'precipitation_mm' => round((float) $current['precipitation'], 1),
            'wind_speed_kmh' => round((float) $current['wind_speed_10m'], 1),
            'weather_code' => $code,
            'condition' => $this->conditionForCode($code),
            'observed_at' => $current['time'],
        ];
    }

    private function conditionForCode(int $code): string
    {
        if ($code === 0) {
            return 'Clear sky';
        }
        if (in_array($code, [1, 2, 3], true)) {
            return 'Partly cloudy';
        }
        if (in_array($code, [45, 48], true)) {
            return 'Fog';
        }
        if ($code >= 51 && $code <= 67) {
            return 'Rain';
        }
        if ($code >= 71 && $code <= 77) {
            return 'Snow';
        }
        if ($code >= 80 && $code <= 82) {
            return 'Rain showers';
        }
        if ($code >= 85 && $code <= 86) {
            return 'Snow showers';
        }
        if ($code >= 95) {
            return 'Thunderstorm';
        }

        return 'Variable conditions';
    }

    private function diningTip(float $temperature, float $precipitation, int $code): string
    {
        if ($precipitation > 0 || ($code >= 51 && $code <= 99)) {
            return 'Rain or snow is around, so a warm local classic or soup is a cosy pick for delivery.';
        }
        if ($temperature >= 28) {
            return 'It is warm in Sarajevo; a fresh salad, lighter bowl, and a cool drink may hit the spot.';
        }
        if ($temperature <= 5) {
            return 'It is chilly outside; consider a hearty Bosnian meal or a warming soup.';
        }

        return 'Conditions look comfortable; choose a neighbourhood favourite and enjoy the day.';
    }
}
