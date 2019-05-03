<?php
declare(strict_types=1);

namespace Willow\Controllers\Hello;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ResponseBody;
use Willow\Models\Hello;

class HelloGetAction
{
    /**
     * @var Hello
     */
    protected $hello;

    public function __construct(Hello $hello)
    {
        $this->hello = $hello;
    }

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface
    {
        /** @var ResponseBody $responseBody */
        $responseBody = $request->getAttribute('response_body');

        $model = $this->hello->find($args['id']);
        if ($model === null) {
            $data = null;
            $status = 404;
        } else {
            $data = $model->toArray();
            $status = 200;
        }

        $responseBody = $responseBody
            ->setData($data)
            ->setStatus($status);
        return $responseBody();
    }
}