<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;

/**
 * Class SearchActionBase
 */
class SearchActionBase extends ActionBase
{
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $model = $this->model;
        $modelColumns = $model::FIELDS;

        // Get the request to build the query
        $parsedBody = $responseBody->getParsedRequest();

        // WHERE Section
        $where = $parsedBody['where'];
        foreach ($where as $item) {
            $column = $item['column'];
            $comparison = $item['comparison'] ?? '=';
            $value = $item['value'];
            $model = $model->where($column, $comparison, $value);
        }

        // ORDER_BY Section (optional)
        if (array_key_exists('order_by', $parsedBody)) {
            foreach ($parsedBody['order_by'] as $orderBy) {
                $model = $model->orderBy($orderBy['column'], $orderBy['direction']);
            }
        }

        // LIMIT Section (optional)
        if (array_key_exists('limit', $parsedBody)) {
            $model = $model->limit($parsedBody['limit']);
        }

        // JOIN Section (optional)
        // @see https://laravel.com/docs/6.x/queries#joins
        if (array_key_exists('join', $parsedBody)) {
            foreach ($parsedBody['join'] as $join) {
                $table = $join['table'];
                $first= $join['first'];
                $operator = $join['operator'] ?? null;
                $second = $join['second'];
                $type = $join['type'] ?? 'inner';
                $model = $model->join($table, $first, $operator, $second, $type, false);
            }
        }

        // TRASHED Section [SOFT DELETES]
        // withTrashed
        if (array_key_exists('with_trashed', $parsedBody)) {
            if ($parsedBody['with_trashed']) {
                $model = $model->withTrashed();
            }
        }

        // onlyTrashed
        if (array_key_exists('only_trashed', $parsedBody)) {
            if ($parsedBody['only_trashed']) {
                $model = $model->onlyTrashed();
            }
        }

        // Perform the query
        $models = $model->get();

        // Did we get any results?
        if ($models !== null && count($models) > 0) {
            $data = $models->toArray();
            foreach ($data as &$datum) {
                $this->sanitize($datum, $modelColumns);
            }

            $status = ResponseBody::HTTP_OK;
        } else {
            $data = null;
            $status = ResponseBody::HTTP_NOT_FOUND;
        }

        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status);
        return $responseBody();
    }
}
