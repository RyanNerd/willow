<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class ResponseBodyFactory
{
    /**
     * Inject a new ResponseBody object into the middleware setting the deserialized request array.
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        // Add 'response_body' attribute to the $request
        // The 'response_body' is a ResponseBody object
        // with parseBody, queryParams, and id argument as a deserialized array
        return $handler
            ->handle(
                $request
                ->withAttribute(
                    'response_body',
                    self::create(
                        array_merge(
                            ['id' => RouteContext::fromRequest($request)->getRoute()->getArgument('id')],
                            $request->getQueryParams() ?? [],
                            $request->getParsedBody() ?? []
                        )
                    )
                )
            );
    }

    /**
     * @param array $parsedRequest
     * @return ResponseBody
     */
    public static function create(array $parsedRequest): ResponseBody {
        return new ResponseBody($parsedRequest);
    }
}
