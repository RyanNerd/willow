<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ProcessCors
{
    /**
     * Process CORS handling
     * @see https://www.slimframework.com/docs/v4/cookbook/enable-cors.html
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     * phpcs:disable
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, OPTIONS, DELETE');
    }
}
