<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class JsonBodyParserMiddleware
 *
 * @see https://github.com/slimphp/Slim/issues/2653#issuecomment-490138033
 */
class JsonBodyParser implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        // Is the content type JSON?
        if (strstr($contentType, 'application/json')) {
            // Try to get the JSON body as an associative array
            $contents = json_decode(file_get_contents('php://input'), true);

            // Is the JSON body valid?
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            } else {
                // Short circuit the request by returning a response with status of 400 (invalid request).
                $responseBody = new ResponseBody();
                $responseBody = $responseBody->setStatus(400)->setMessage('Invalid JSON');
                return $responseBody();
            }
        } else {
            $method = $request->getMethod();

            // If the method is POST or PATCH the ONLY valid Content-Type is application/json
            if ($method === 'POST' || $method === 'PATCH') {
                // Short circuit the request by returning a response with status of 400 (invalid request).
                $responseBody = new ResponseBody();
                $responseBody = $responseBody->setStatus(400)->setMessage("Invalid Content-Type: $contentType");
                return $responseBody();
            }
        }

        return $handler->handle($request);
    }
}