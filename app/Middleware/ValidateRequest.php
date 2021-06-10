<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ValidateRequest
{
    /**
     * Validation middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');

        // Example code:
        // Put your logic here to determine if request is authorized and/or is admin
        // phpcs:ignore
        if (true) {
            $responseBody = $responseBody
                ->setIsAdmin()
                ->setIsAuthenticated();
            return $handler->handle($request->withAttribute('response_body', $responseBody));
        }

        // Short circuit the request by returning a response with status of 401;
        // phpcs:ignore
        $responseBody = $responseBody->setStatus(401)->setMessage('Invalid API Key');
        return $responseBody();
    }
}
