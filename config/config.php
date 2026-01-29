<?php
declare(strict_types=1);

use App\Config\Env;

return [
    'server'   => Env::get('DB_SERVER'),
    'port'     => Env::get('DB_PORT'),
    'database' => Env::get('DB_DATABASE'),
    'username' => Env::get('DB_USERNAME'),
    'password' => Env::get('DB_PASSWORD'),
    'encrypt'  => Env::bool('DB_ENCRYPT', false),
    'trust'    => Env::bool('DB_TRUST_SERVER_CERT', true),
];
