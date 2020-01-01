<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

class SearchValidatorBase
{
    protected const VALID_COMPARISON_STRINGS = [
        '=',
        '>',
        '<',
        '>=',
        '<=',
        '<>',
        'LIKE',
        'like'
    ];

    protected const VALID_CLAUSES = [
        'id',
        'api_key',
        'where',
        'order_by',
        'limit',
        'with_trashed',
        'only_trashed'
    ];

    /**
     * @var ModelBase
     */
    protected $model;

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedBody = $responseBody->getParsedRequest();
        $model = $this->model;

        $parsedKeys= array_keys($parsedBody);

        // where may be required
        if (!$model->allowAll) {
            if (!in_array('where', $parsedKeys)) {
                $responseBody->registerParam('required', 'where', 'array<object>');
            }
        }

        $invalidClauses = array_diff($parsedKeys, self::VALID_CLAUSES);
        foreach ($invalidClauses as $invalidClause) {
            $responseBody->registerParam('invalid', $invalidClause, null);
        }

        $where = $parsedBody['where'] ?? [];
        foreach ($where as $item) { // FIXME: PHP Notice:  Undefined index: where in /var/www/rxchart-app/app/Controllers/SearchValidatorBase.php on line 46
            $column = $item['column'] ?? '';

            // Check the white listed columns for the model.
            // If the column is not in the white list then register it as invalid.
            if (!array_key_exists($column, $model::FIELDS)) {
                $responseBody->registerParam('invalid', $column, 'column');
            } else {
                if (!array_key_exists('column', $item)) {
                    $responseBody->registerParam('required', 'where->column', 'string');
                }

                if (!array_key_exists('value', $item)) {
                    $responseBody->registerParam('required', 'where->value', 'string');
                }

                // Is a comparison item given?
                if (array_key_exists('comparison', $item)) {
                    // Make sure the comparison string is valid
                    if (!in_array($item['comparison'], self::VALID_COMPARISON_STRINGS)) {
                        $responseBody->registerParam('invalid', 'where->comparison', 'string');
                    }
                }
            }
        }

        // Is limit requested?
        if (array_key_exists('limit', $parsedBody)) {
            // The limit value MUST be an integer.
            if (!is_int($parsedBody['limit'])) {
                $responseBody->registerParam('invalid', 'limit', 'integer');
            }
        }

        // If any missing required or invalid then respond with invalid request.
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(400);
            return $responseBody();
        }

        return $handler->handle($request);
    }
}
