<?php
declare(strict_types=1);

use Dotenv\Dotenv;

$viridianPath = __DIR__ . '/../.viridian';
if (file_exists($viridianPath)) {
    $dotViridian = Dotenv::createImmutable(__DIR__ . '/../', '.viridian');
    $viridian = $dotViridian->safeLoad();
} else {
    $viridian = [];
}

return  [
    'viridian' => $viridian
];
