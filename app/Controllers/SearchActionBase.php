<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;

class SearchActionBase extends ActionBase
{
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @link https://laravel.com/docs/8.x/queries
     */
    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $model = clone $this->model;

        // Get the request to build the query
        $parsedBody = $responseBody->getParsedRequest();

        // Special handling for withTrashed or onlyTrashed
        if (array_key_exists('withTrashed', $parsedBody)) {
            $model = $this->model::withTrashed(true);
        } elseif (array_key_exists('onlyTrashed', $parsedBody)) {
            $model = $this->model::onlyTrashed();
        } else {
            $model = $this->model;
        }
        $model = $model->clone();

        foreach ($parsedBody as $key => $value) {
            // Handle situations when there are no parameters, or keys that should be skipped,
            // execute model method `$model->$key([$params])` as default action.
            switch ($key) {
                case 'withTrashed':
                case 'onlyTrashed':
                    break;
                case 'id':      // Ignore id
                    break;      // continue
                default:
                    if (is_array($value)) {
                        foreach ($value as $params) {
                            if (is_array($params)) {
                                $model = $model->$key(...$params);
                            } else {
                                $model = $model->$key($params);
                            }
                        }
                    } else {
                        // Invalid parameters
                        $responseBody = $responseBody
                            ->setData(null)
                            ->setStatus(ResponseBody::HTTP_BAD_REQUEST)
                            ->setMessage('invalid parameters for: ' . $key);
                        return $responseBody();
                    }
            }
        }

        // Perform the query
        $models = $model->get();

        // Did we get any results?
        if ($models !== null && count($models) > 0) {
            $data = $models->toArray();
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
