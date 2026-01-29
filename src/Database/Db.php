<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

class Db
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {

            $db = require __DIR__ . '/../../config/config.php';

            $encrypt = $db['encrypt'] ? 'yes' : 'no';
            $trust   = $db['trust'] ? 'yes' : 'no';

            /*$dsn = sprintf(
                'sqlsrv:Server=%s;Database=%s;Encrypt=%s;TrustServerCertificate=%s',
                $db['server'],
                $db['database'],
                $encrypt,
                $trust
            );*/

            self::$pdo = new PDO(
                "sqlsrv:Server={$db['server']};Database={$db['database']};TrustServerCertificate=true",
                $db['username'],
                $db['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$pdo;
    }
}
