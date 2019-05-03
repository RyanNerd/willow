<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\TableAlias;

class TableAliasGetAction
{
    /**
     * @var TableAlias
     */
    protected $model;

    /**
     * Get the model via Dependency Injection and save it.
     *
     * @param TableAlias $model
     */
    public function __construct(TableAlias $model)
    {
        $this->model = $model;
    }

    /**
     * Handle GET request
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

        // Load the model with the given id (PK)
        $model = $this->model->find($args['id']);

        // If the record is not found then 404 error, otherwise status is 200.
        if ($model === null) {
            $data = null;
            $status = 404;
        } else {
            $data = $model->toArray();
            $status = 200;
        }

        // Set the status and data of the ResponseBody
        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status);

        // Return the response as JSON
        return $responseBody();
    }
}