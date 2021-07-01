<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;

abstract class WriteActionBase extends ActionBase
{
    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $body = $responseBody->getParsedRequest();
        $model = $this->model;

        $primaryKeyName = $model->getPrimaryKey();

        // Get all the attributes for all the columns for the model.
        $columnAttributes = ModelValidatorBase::getColumnAttributes($this->model::class);
        $columnNames = array_keys($columnAttributes);

        // Replace each key value from the parsed request into the model and save.
        foreach ($body as $key => $value) {
            // Ignore Primary Key
            if ($key === $primaryKeyName) {
                continue;
            }

            // Ignore timestamps if Eloquent is handling this on the back-end.
            if ($model->timestamps) {
                if ($key === $model::CREATED_AT || $key === $model::UPDATED_AT) {
                    continue;
                }
            }

            // Only update fields listed in the columnNames array
            if (in_array($key, $columnNames, true)) {
                $model->$key = $value;
            }
        }

        // Save the model to the database and return the resulting model as the response
        if ($model->save()) {
            $responseBody = $responseBody
                ->setData($model->attributesToArray())
                ->setStatus(ResponseBody::HTTP_OK);
        } else {
            // Unable to save for some reason so we return error status
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(ResponseBody::HTTP_INTERNAL_SERVER_ERROR)
                ->setMessage('Unable to save changes to ' . $model->getTable());
        }
        return $responseBody();
    }
}
