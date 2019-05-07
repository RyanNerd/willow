<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class JsonBodyParserMiddleware
 *
 * @see https://github.com/slimphp/Slim/issues/2653#issuecomment-490138033
 */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }

        return $handler->handle($request);
    }
}