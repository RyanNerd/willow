<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;

abstract class RestoreActionBase extends ActionBase
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');

        $record = $this
            ->model
            ->withTrashed()
            ->find($args['id']);

        // Did we find a record to restore? Try to restore record, otherwise return status 404.
        if ($record !== null) {
            // Was the record successfully restored? Return the record and status of 200, otherwise return status 500;
            if ($record->restore()) {
                $data = $record->toArray();
                $status = ResponseBody::HTTP_OK;
            } else {
                $data = null;
                $status = ResponseBody::HTTP_INTERNAL_SERVER_ERROR;
            }
        } else {
            $status = ResponseBody::HTTP_NOT_FOUND;
            $data = null;
        }

        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status);
        return $responseBody();
    }
}
