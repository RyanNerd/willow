<?php
declare(strict_types=1);

use Dotenv\Dotenv;

return [
    'viridian' => function () {
        if (file_exists(__DIR__ . '/../.viridian')) {
            $dotViridian = Dotenv::createImmutable(__DIR__ . '/../', '.viridian');
            return $dotViridian->safeLoad();
        } else {
            return [];
        }
    }
];
