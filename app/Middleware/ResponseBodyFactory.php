<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\Route;

class ResponseBodyFactory
{
    /**
     * @var ResponseBody
     */
    protected $responseBody;

    /**
     * ResponseBodyFactory constructor.
     *
     * @param ResponseBody $responseBody
     */
    public function __construct(ResponseBody $responseBody)
    {
        $this->responseBody = $responseBody;
    }

    /**
     * Inject a new ResponseBody object into the middleware setting the deserialized request array.
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');
        $id = $route->getArgument('id');

        // Get the body and query parameters as a deserialized array
        $parsedBody = $request->getParsedBody() ?? [];
        $queryParameters = $request->getQueryParams();

        $this->responseBody = $this->responseBody->setParsedRequest(array_merge(['id' => $id], $queryParameters, $parsedBody));
        $request = $request->withAttribute('response_body', $this->responseBody);
        return $handler->handle($request);
    }
}