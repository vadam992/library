<?php
declare(strict_types=1);

namespace App\Http;

/**
 * Request
 *
 * HTTP kéréshez kapcsolódó segédmetódusokat tartalmazó osztály.
 * Feladata a request alapadatainak egységes és biztonságos
 * kiolvasása (method, path, query paraméterek, JSON body).
 */
final class Request
{
    /**
     * HTTP metódus lekérése.
     *
     * @return string A kérés HTTP metódusa (GET, POST, PUT, DELETE, ...)
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * A kérés URL útvonalának (path) lekérése.
     *
     * A query stringet (pl. ?search=...) eltávolítja,
     * és egységes formátumot biztosít a router számára.
     *
     * @return string Az URL path része (pl. /api/books)
     */
    public static function path(): string
    {
        // Teljes URI lekérése
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Csak az útvonal rész kivágása (query string nélkül)
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Záró perjel egységesítése
        return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
    }

    /**
     * JSON request body beolvasása.
     *
     * A php://input streamből olvassa ki a nyers adatot,
     * majd megpróbálja JSON-né dekódolni.
     *
     * @return array A dekódolt JSON body, vagy üres tömb
     */
    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');

        // Ha nincs body, üres tömbbel térünk vissza
        if (!$raw) {
            return [];
        }

        // JSON dekódolás asszociatív tömbbé
        $data = json_decode($raw, true);

        // Csak akkor térünk vissza adattal, ha az valóban tömb
        return is_array($data) ? $data : [];
    }

    /**
     * Query paraméter lekérése.
     *
     * Példa: /api/books?search=orwell
     *
     * @param string $key A query paraméter neve
     * @param string|null $default Alapértelmezett érték, ha nem létezik
     * @return string|null A paraméter értéke vagy a default
     */
    public static function query(string $key, ?string $default = null): ?string
    {
        return isset($_GET[$key]) ? (string)$_GET[$key] : $default;
    }
}
