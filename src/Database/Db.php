<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Db
 *
 * Egyszerű adatbázis-kapcsolat kezelő osztály.
 * Singleton-szerű megoldást használ, hogy az alkalmazás
 * során csak egyetlen PDO kapcsolat jöjjön létre.
 */
class Db
{
    /**
     * A megosztott PDO kapcsolat példánya.
     * Statikus, hogy az alkalmazás minden részében
     * ugyanaz a kapcsolat legyen használva.
     */
    private static ?PDO $pdo = null;

    /**
     * PDO kapcsolat lekérése.
     *
     * Ha még nem létezik kapcsolat, létrehozza azt a
     * konfigurációs fájlban megadott adatok alapján.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        // Ha még nincs aktív kapcsolat, létrehozzuk
        if (self::$pdo === null) {

            // Adatbázis konfiguráció betöltése
            $db = require __DIR__ . '/../../config/config.php';

            // PDO kapcsolat létrehozása SQL Serverhez
            self::$pdo = new PDO(
                // DSN: SQL Server + adatbázis név
                "sqlsrv:Server={$db['server']};Database={$db['database']};TrustServerCertificate=true",

                // Hitelesítési adatok
                $db['username'],
                $db['password'],

                // PDO opciók
                [
                    // Hibák kivételként kezelése
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Alapértelmezett fetch mód asszociatív tömb
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Prepared statementek natív használata
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        // Meglévő vagy újonnan létrehozott kapcsolat visszaadása
        return self::$pdo;
    }
}
