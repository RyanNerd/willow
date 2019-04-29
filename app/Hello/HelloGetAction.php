<?php
declare(strict_types=1);

namespace Willow\Hello;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
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
        $id = $args['id'];
        $model = $this->hello->find($id);
        if ($model === null) {
            $data = null;
            $status = 404;
        } else {
            $data = $model->toArray();
            $status = 200;
        }

        return $this->sendResponse($response, $data, $status);
    }

    protected function sendResponse(Response $response, ?array $data, int $status): ResponseInterface
    {
        $payload = [
            'success' => ($status === 200),
            'data' => $data,
            'status' => $status
        ];
        $response
            ->getBody()
            ->write(json_encode($payload));
        return $response->withHeader('content-type', 'application/json');
    }
}