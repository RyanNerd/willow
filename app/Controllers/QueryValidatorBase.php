<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\ResponseBody;
use Slim\Routing\Route;

abstract class QueryValidatorBase
{
    /**
     * @var array Model::FIELDS
     */
    protected $modelFields = [];

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedRequest = $responseBody->getParsedRequest();

        /** @var Route $route */
        $route = $request->getAttribute('route');
        $value = $route->getArgument('value');

        switch ($value)
        {
            case '*':
                break;

            case '_':
                foreach ($parsedRequest as $item => $value) {
                    if ($item{2} === '__') {
                        $columnName = substr($item, 2);
                        if (!array_key_exists($columnName, $this->modelFields)) {
                            $responseBody->registerParam('invalid', $columnName, null);
                        }
                    }
                }
                break;

            default:
                if (!array_key_exists($parsedRequest['column_name'], $this->modelFields)) {
                    $responseBody->registerParam('invalid', 'column_name', 'string');
                }
                break;
        }

        // Are there any missing or required request data points?
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            // Set the response body to invalid request status and short circuit any further processing
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(400);
            return $responseBody();
        }

        // All validations passed so we continue to process the request.
        return $handler->handle($request);
    }
}