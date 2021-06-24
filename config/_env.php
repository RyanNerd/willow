<?php
declare(strict_types=1);

use Dotenv\Dotenv;

return [
    function () {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/../');
        $env = $dotEnv->load();

        // DB_DRIVER and DB_NAME are required and cannot be empty
        $dotEnv->required(['DB_DRIVER','DB_NAME'])->notEmpty();

        // All drivers except for SQLite require DB_HOST, DB_PORT, DB_USER, and DB_PASSWORD and these cannot be empty.
        if ($env['DB_DRIVER'] !== 'sqlite') {
            $dotEnv->required(['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASSWORD'])->notEmpty();
        }

        $dotEnv->ifPresent('PRODUCTION')->allowedValues(['true', 'false']);
        $dotEnv->ifPresent('SHOW_ERRORS')->allowedValues(['true', 'false']);
        return $env;
    }
];
