<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

abstract class QueryActionBase extends ActionBase
{
    /**
     * @var ModelBase
     */
    protected $model;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var bool When set to true it allows queries with the value of * to return all records for the table.
     */
    protected $allowAll = false;

    /**
     * @var array Set to an array of column names and directions ['column_name1' => 'asc', 'column_name2' => 'desc']
     */
    protected $orderBy = [];

    /**
     * @var array Set to an array of column names to group by
     */
    protected $groupBy = [];

    /**
     * Handle GET query request
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $parsedRequest = $responseBody->getParsedRequest();

        $value = $args['value'];
        $models = null;

        switch ($value) {
            // SELECT *
            case '*':
            {
                if ($this->allowAll) {
                    $models = $this->model;

                    // Do we have any relations we need to add to the query?
                    if (count($this->relations) > 0) {
                        $models = $models->with($this->relations);
                    }

                    // Is there a default orderBy?
                    if (count($this->orderBy) > 0) {
                        foreach ($this->orderBy as $column => $direction) {
                            $models = $models->orderBy($column, $direction);
                        }
                    }

                    // Do we have any group by columns?
                    if (count($this->groupBy) > 0) {
                        $models = $models->groupBy($this->groupBy);
                    }

                    $models = $models
                        ->get()
                        ->all();
                }
                break;
            }

            // SELECT * WHERE _columnName=value AND _columnName=value [...]
            case '_':
            {
                $model = $this->model;
                foreach ($parsedRequest as $item => $fieldValue) {
                    if ($item{0} === '_') {
                        $columnName = substr($item, 1);
                        $model = $model->where($columnName, '=', $fieldValue);
                    }
                }

                // Do we have any relations we need to add to the query?
                if (count($this->relations) > 0) {
                    $model = $model->with($this->relations);
                }

                // Is there a default orderBy?
                if (count($this->orderBy) > 0) {
                    foreach ($this->orderBy as $column => $direction) {
                        $model = $model->orderBy($column, $direction);
                    }
                }

                // Do we have any group by columns?
                if (count($this->groupBy) > 0) {
                    $model = $model->groupBy($this->groupBy);
                }

                $models = $model->get();
                break;
            }

            // SELECT * WHERE `column_name` `operator` `value`
            default:
            {
                $columnName = $parsedRequest['column_name'];
                $operator = $parsedRequest['operator'] ?? '=';
                $model = $this->model;

                // Do we have any relations that need to be added to the query?
                if (count($this->relations) > 0) {
                    $model = $model->with($this->relations);
                }

                // Is there a default orderBy?
                if (count($this->orderBy) > 0) {
                    foreach ($this->orderBy as $column => $direction) {
                        $model = $model->orderBy($column, $direction);
                    }
                }

                $models = $model
                    ->where($columnName, $operator, $value)
                    ->get();
            }
        }

        // Do we have a collection of models?
        if ($models !== null) {
            // Is there at least 1 model in the collection?
            if (count($models) > 0) {
                $dataTables = [];

                // Convert the collection of models into a standard array and strip any protected fields.
                /** @var ModelBase $model */
                foreach ($models as $model) {
                    $data = $model->toArray();
                    $this->sanitize($data, $model::FIELDS);
                    $dataTables[] = $data;
                }

                $responseBody = $responseBody
                    ->setData($dataTables)
                    ->setStatus(200);
            } else {
                $responseBody = $responseBody
                    ->setData(null)
                    ->setStatus(404);
            }
        } else {
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(400)
                ->setMessage('Invalid request');
        }

        return $responseBody();
    }
}