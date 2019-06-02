<?php
declare(strict_types=1);

use Willow\Main\App;

require __DIR__ . '/../vendor/autoload.php';

// Does the .env file exist?
if (file_exists(__DIR__ . '/../.env')) {
    // Set the environment variables from the .env file
    require_once __DIR__ . '/../config/_env.php';

    // Is this a CORS pre-flight request and should we handle it?
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && getenv('CORS') === 'true') {
        ob_start();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');

        header(sprintf(
            'HTTP/%s %s %s',
            "1.1",
            200,
            'OK'
        ));
        ob_end_flush();
        exit();
    }
} else {
    // Output to STDERR that the .env file is missing.
    $stdErr = fopen('php://stderr', 'b');
    fwrite($stdErr, 'WARNING: The .env file is missing' . PHP_EOL);
    fclose($stdErr);
}

// Launch the app
$app = new App();
$app();
