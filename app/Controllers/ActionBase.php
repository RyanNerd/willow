<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

abstract class ActionBase
{
    /**
     * @var ModelBase
     */
    protected $model;

    public function __invoke(Request $request, Response $response): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');
        $body = $responseBody->getParsedRequest();
        $model = $this->model;

        // Does the request body have an Id?
        if ($body['id'] !== null) {
            // Look up the model record.
            $model = $model->find($body['Id']);

            // If we couldn't find the record then respond with 404 (not found) status.
            if ($model === null) {
                $responseBody = $responseBody
                    ->setData(null)
                    ->setStatus(404);
                return $responseBody();
            }
        }

        // Replace each key value from the parsed request into the model and save.
        $columns = array_keys($model::FIELDS);
        foreach ($body as $key => $value) {
            // Ignore Primary Key
            if ($key === $model->getPrimaryKey()) {
                continue;
            }

            // Ignore timestamps
            if ($model->timestamps) {
                if ($key === $model::CREATED_AT || $key === $model::UPDATED_AT) {
                    continue;
                }
            }

            // Only update fields listed in the model::FIELDS array
            if (in_array($key, $columns)) {
                $model->$key = $value;
            }
        }

        // Call the beforeSave event hook
        $this->beforeSave($model);

        // Update the model on the database.
        if ($model->save()) {
            $responseBody = $responseBody
                ->setData($model->toArray())
                ->setStatus(200);
        } else {
            // Unable to save for some reason so we return error status.
            $responseBody = $responseBody
                ->setData(null)
                ->setStatus(500)
                ->setMessage('Unable to save changes to ' . $model->getTableName());

        }

        return $responseBody();
    }

    /**
     * Override this function if you need to make changes to the model prior to saving.
     *
     * @param ModelBase $model
     */
    protected function beforeSave(ModelBase &$model) {}
}
