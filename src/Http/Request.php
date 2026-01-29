<?php
declare(strict_types=1);

namespace App\Http;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
    }

    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function query(string $key, ?string $default = null): ?string
    {
        return isset($_GET[$key]) ? (string)$_GET[$key] : $default;
    }
}
