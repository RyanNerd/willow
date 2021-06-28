<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\ResponseBody;

class SearchValidatorBase extends ActionBase
{
    private const ALLOWED_METHODS = [
        'select',
        'where',
        'orWhere',
        'whereBetween',
        'orWhereBetween',
        'whereNotBetween',
        'orWhereNotBetween',
        'whereIn',
        'whereNotIn',
        'orWhereIn',
        'orWhereNotIn',
        'whereNull',
        'whereNotNull',
        'orWhereNull',
        'orWhereNotNull',
        'whereDate',
        'whereMonth',
        'whereDay',
        'whereYear',
        'whereTime',
        'whereColumn',
        'orWhereColumn',
        'orderByDesc',
        'orderBy',
        'limit',
        'latest',
        'first',
        'skip',
        'take',
        'offset',
        'inRandomOrder',
        'groupBy',
        'having',
        'distinct',
        'join',
        'leftJoin',
        'rightJoin',
        'crossJoin',
        'sharedLock'
    ];

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedBody = $responseBody->getParsedRequest();
        $model = $this->model;
        $parsedKeys = array_keys($parsedBody);

        // where may be required
        if (!$model->allowAll) {
            if (!in_array([
                    'where',
                    'orWhere',
                    'whereBetween',
                    'orWhereBetween',
                    'whereNotBetween',
                    'orWhereNotBetween',
                    'whereIn',
                    'whereNotIn',
                    'orWhereIn',
                    'orWhereNotIn',
                    'whereNull',
                    'whereNotNull',
                    'orWhereNull',
                    'orWhereNotNull',
                    'whereDate',
                    'whereMonth',
                    'whereDay',
                    'whereYear',
                    'whereTime',
                    'whereColumn'
                ], $parsedKeys)) {
                $responseBody->registerParam('required', 'where', 'array<object>');
            }
        }

        foreach ($parsedKeys as $key) {
            if (!in_array($key, self::ALLOWED_METHODS)) {
                $responseBody->registerParam('invalid', $key, null);
            }
        }

        // If any missing required or invalid then respond with invalid request.
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(ResponseBody::HTTP_BAD_REQUEST);
            return $responseBody();
        }

        return $handler->handle($request);
    }
}
