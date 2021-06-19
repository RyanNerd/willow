<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Willow\Middleware\ResponseBody;

interface IRules
{
    public function __invoke(ResponseBody $responseBody, array $fields): ResponseBody;
}
