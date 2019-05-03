<?php
declare(strict_types=1);

namespace Willow\Controllers\Sample;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;

class SampleGetAction
{
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

        $responseBody = $responseBody
            ->setData(['id' => $args['id']])
            ->setMessage('Sample test')
            ->setStatus(200);
        return $responseBody();
    }
}