<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\ResponseBody;
use Willow\Utilities\DocBlockParser;

abstract class ValidatorBase
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var DocBlockParser
     */
    protected $docBlockParser;

    public function __construct(DocBlockParser $docBlockParser)
    {
        $this->docBlockParser = $docBlockParser;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedRequest = $responseBody->getParsedRequest();

        $this->processValidation($responseBody, $parsedRequest);

        // If there are any missing or required data points then we short circuit and return invalid request.
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            $responseBody = $responseBody
                ->setStatus(400)
                ->setMessage('Missing or invalid request');
            return $responseBody();
        } else {

        }
        return $handler->handle($request);
    }

    /**
     * You should override this function to perform the validations
     *
     * @param ResponseBody $responseBody
     */
    protected function processValidation(ResponseBody &$responseBody, array &$parsedRequest) {}

    /**
     * Given the model::class return an associative array of fields for the model with the data type.
     *
     * @param string $modelClass
     * @return array
     * @throws \ReflectionException
     */
    protected function getModelFields(string $modelClass): array
    {
        return $this->docBlockParser->getDocProperties($modelClass);
    }
}