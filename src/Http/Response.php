<?php
declare(strict_types=1);

namespace App\Http;

/**
 * Response
 *
 * HTTP válaszok egységes kezelésére szolgáló segédosztály.
 * Feladata a JSON válaszok és a megfelelő HTTP státuszkódok
 * beállítása és visszaküldése a kliens felé.
 */
final class Response
{
    /**
     * JSON válasz küldése.
     *
     * Beállítja a HTTP státuszkódot és a Content-Type fejlécet,
     * majd JSON formátumban kiírja az adatokat és leállítja
     * a script futását.
     *
     * @param mixed $data A válasz törzse (JSON-né alakítva)
     * @param int $status HTTP státuszkód (alapértelmezett: 200)
     */
    public static function json(mixed $data, int $status = 200): void
    {
        // HTTP státuszkód beállítása
        http_response_code($status);

        // JSON válasz fejléc
        header('Content-Type: application/json; charset=utf-8');

        // Adatok JSON-né alakítása és kiírása
        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        // A válasz elküldése után a script leállítása
        exit;
    }

    /**
     * Hibaválasz küldése egységes formátumban.
     *
     * @param string $message Hibaleírás
     * @param int $status HTTP státuszkód (alapértelmezett: 400)
     * @param array $extra Opcionális kiegészítő adatok
     */
    public static function error(
        string $message,
        int $status = 400,
        array $extra = []
    ): void {
        // A hibaválasz is JSON formátumban kerül visszaadásra
        self::json(
            ['error' => $message] + $extra,
            $status
        );
    }
}
