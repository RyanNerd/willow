<?php
declare(strict_types=1);

use Dotenv\Dotenv;

$dotEnv = Dotenv::createImmutable(__DIR__ . '/../');
$env = $dotEnv->load();
$dotEnv->required([
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'DISPLAY_ERROR_DETAILS'
])->notEmpty();
$dotEnv->required('DISPLAY_ERROR_DETAILS')->allowedValues(['true', 'false']);
return  ['ENV' => $env];