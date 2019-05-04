<?php
declare(strict_types=1);

namespace Willow\Controllers\Sample;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as V;
use Willow\Middleware\ResponseBody;

class SampleGetValidator
{
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedBody = $responseBody->getParsedRequest();

        // Reject Roman numerals as the id
        if (V::roman()->validate($parsedBody['id'])) {
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(400)
                ->setMessage('Roman numerals are not allowed.');
            return $responseBody();
        }

        return $handler->handle($request);
    }
}
