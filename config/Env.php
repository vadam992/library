<?php
declare(strict_types=1);

namespace App\Config;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) return;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $v = getenv($key);
        return $v === false ? $default : $v;
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, (string)$default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = strtolower((string) self::get($key, $default ? '1' : '0'));
        return in_array($v, ['1','true','yes','on'], true);
    }
}
