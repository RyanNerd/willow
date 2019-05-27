<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Controllers\GetActionBase;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

final class GetActionTest extends TestCase
{
    public function testGetAction(): void
    {
        $arg = ['id' => 1];
        $model = new MockModel(false);
        $mockGetAction = new MockGetAction($model);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $responseBody = new ResponseBody();

        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $result = $mockGetAction->__invoke($request, $response, $arg);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $json = json_decode($body, true);

        $this->assertEquals(['id' => 1, 'test' => true, 'extra' => 32.3], $json['data']);
        $this->assertEquals(200, $json['status']);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testGetAction404(): void
    {
        $arg = ['id' => 1];
        $model = new MockModel(true);
        $mockGetAction = new MockGetAction($model);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $responseBody = new ResponseBody();

        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $result = $mockGetAction->__invoke($request, $response, $arg);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $json = json_decode($body, true);

        $this->assertEquals(null, $json['data']);
        $this->assertEquals(404, $json['status']);
        $this->assertEquals(404, $result->getStatusCode());
    }
}

class MockGetAction extends GetActionBase
{
    public function __construct(MockModel $model)
    {
        $this->model = $model;
    }

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface
    {
        return parent::__invoke($request, $response, $args);
    }
}

class MockModel extends ModelBase
{
    public const FIELDS = [
        'id' => 'integer',
        'test' => 'boolean',
        'protected' => '*string'
    ];

    protected $findFail = false;

    public function __construct(bool $findFail)
    {
        $this->findFail = $findFail;
    }

    public function find($id, $columns = ['*'])
    {
        if ($this->findFail) {
            return null;
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => 1,
            'test' => true,
            'protected' => 'do not show',
            'extra' => 32.3
        ];
    }
}