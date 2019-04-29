<?php
declare(strict_types=1);

use Dotenv\Dotenv;

$dotEnv = Dotenv::create(__DIR__ . '/../');
$dotEnv->load();
$dotEnv->required(
    [
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'DISPLAY_ERROR_DETAILS'
    ]
);