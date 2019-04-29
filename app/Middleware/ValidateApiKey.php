<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidateApiKey
{
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $qp = $request->getQueryParams();
        $key = $qp['key'] ?? '';

        // Placeholder logic
        if ($key === 'stop') {
            $response = new Response();
                $response->withHeader('content-type', 'application\json')
                ->getBody()
                ->write(json_encode(['message' => 'Invalid API']));
            return $response;
        }

        // Keep processing middleware queue as normal
        return $handler->handle($request);
    }
}