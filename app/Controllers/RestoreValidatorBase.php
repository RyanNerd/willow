<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\ResponseBody;

class RestoreValidatorBase
{
    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedBody = $responseBody->getParsedRequest();

        // Does the id key exist in the request? If not then register as required.
        if (!key_exists('restore_id', $parsedBody)) {
            $responseBody->registerParam('required', 'id', 'primary key');
        }

        // Is there an invalid or missing parameter in the request? Respond with status 400.
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            $responseBody = $responseBody
                ->setStatus(ResponseBody::HTTP_BAD_REQUEST)
                ->setData(null);
            return $responseBody();
        }

        // Continue processing request
        return $handler->handle($request);
    }
}
