<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Controllers\DeleteActionBase;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

final class DeleteActionTest extends TestCase
{
    public function testDeleteAction(): void
    {
        $arg = ['id' => 1];
        $model = new MockModel();
        $mockDeleteAction = new MockDeleteAction($model);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $responseBody = new ResponseBody();

        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $result = $mockDeleteAction->__invoke($request, $response, $arg);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $json = json_decode($body, true);

        $this->assertNull($json['data']);
        $this->assertEquals(200, $json['status']);
        $this->assertEquals(200, $result->getStatusCode());
    }
}

class MockDeleteAction extends DeleteActionBase
{
    public function __construct(MockModel $model)
    {
        $this->model = $model;
    }
}

class MockModel extends ModelBase
{
    public const FIELDS = [
        'id' => 'integer',
        'test' => 'boolean',
        'protected' => '*string'
    ];

    public function find($id, $columns = ['*'])
    {
        return $this;
    }

    public static function destroy($ids)
    {
        return 1;
    }

    public function save(array $options = [])
    {
        return true;
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