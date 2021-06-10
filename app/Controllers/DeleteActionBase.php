<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

class DeleteActionBase extends ActionBase
{
    /**
     * Handle DELETE request
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');

        /** @var ModelBase $model */
        $model = $this->model;

        $model = $model->find($args['id']);

        if ($model->destroy($args['id']) === 1) {
            $status = ResponseBody::HTTP_OK;
        } else {
            $status = ResponseBody::HTTP_NOT_FOUND;
        }

        // Set the status and data of the ResponseBody
        $responseBody = $responseBody
            ->setData(null)
            ->setStatus($status);

        // Return the response as JSON
        return $responseBody();
    }
}
