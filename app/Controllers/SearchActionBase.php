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
     * @link https://laravel.com/docs/8.x/queries
     */
    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $model = clone $this->model;

        // Get the request to build the query
        $parsedBody = $responseBody->getParsedRequest();
        foreach ($parsedBody as $key => $value) {
            switch ($key) {
                case 'withTrashed':
                    $model = $model->withTrashed();
                    break;
                case 'onlyTrashed':
                    $model = $model->onlyTrashed();
                    break;
                default:
                    if (method_exists($model, $key)) {
                        if (is_array($value)) {
                            $model = $model->$key(...$value);
                        } else {
                            $model = $model->$key($value);
                        }
                    } else {
                        $responseBody = $responseBody
                            ->setData(null)
                            ->setStatus(ResponseBody::HTTP_BAD_REQUEST);
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
