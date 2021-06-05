<?php
declare(strict_types=1);

use Dotenv\Dotenv;

return [
    'ENV' => function () {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/../');
        $env = $dotEnv->load();
        $dotEnv->required([
            'DB_DRIVER',
            'DB_HOST',
            'DB_PORT',
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD',
            'DISPLAY_ERROR_DETAILS'
        ])->notEmpty();
        $dotEnv->required('DISPLAY_ERROR_DETAILS')->allowedValues(['true', 'false']);
        $dotEnv->required('DB_DRIVER')->allowedValues(['mysql', 'pgsql', 'sqlsrv', 'sqlite']);
        return $env;
    }
];
