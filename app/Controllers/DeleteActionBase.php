<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

class DeleteActionBase
{
    /**
     * @var ModelBase
     */
    protected $model;

    /**
     * Handle DELETE request
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

        /** @var ModelBase $model */
        $model = $this->model;

        $model = $model->find($args['id']);

        if ($model === null) {
            $status = 404;
            $data = null;
        } else {
            $model->Active = false;
            if ($model->save()) {
                $status = 200;
                $data = $model;
            } else {
                $status = 400;
                $data = null;
            }
        }

        // Set the status and data of the ResponseBody
        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status);

        // Return the response as JSON
        return $responseBody();
    }
}
