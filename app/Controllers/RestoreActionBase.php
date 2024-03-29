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
class RestoreActionBase extends ActionBase
{
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');

        // Get the record via the id from the parsed request body
        $parsedBody = $responseBody->getParsedRequest();
        $record = $this
            ->model
            ->withTrashed()
            ->find($parsedBody['restore_id']);

        // Default values
        $message = '';

        // Did we find a record to restore? Try to restore record, otherwise return status 404.
        if ($record !== null) {
            // Was the record successfully restored? Return the record and status of 200, otherwise return status 500;
            if ($record->restore()) {
                $data = $record->toArray();
                $status = ResponseBody::HTTP_OK;
            } else {
                $data = null;
                $status = ResponseBody::HTTP_INTERNAL_SERVER_ERROR;
                $message = 'Unable to restore.';
            }
        } else {
            $status = ResponseBody::HTTP_NOT_FOUND;
            $data = null;
        }

        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status)
            ->setMessage($message);
        return $responseBody();
    }
}
