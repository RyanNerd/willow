<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Controllers\WriteActionBase;
use Willow\Middleware\ResponseBody;
use Willow\Models\ModelBase;

final class WriteActionTest extends TestCase
{
    public function testWriteActionInvoke(): void
    {
        $model = new MockModelWriteAction(false);

        $writeAction = new MockWriteAction($model);

        $responseBody = new MockResponseBodyWriteAction();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $response = $this->createMock(Response::class);

        $result = $writeAction($request, $response);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $contents = $bodyStream->getContents();
        $json = json_decode($contents, false);
        $data = $json->data;

        $this->assertEquals('test', $data->extra);
    }

    public function testWriteActionFindFailure(): void
    {
        $model = new MockModelWriteAction(true);

        $writeAction = new MockWriteAction($model);

        $responseBody = new MockResponseBodyWriteAction();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $response = $this->createMock(Response::class);

        $result = $writeAction($request, $response);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $contents = $bodyStream->getContents();
        $json = json_decode($contents, false);
        $data = $json->data;

        $this->assertFalse($json->success);
        $this->assertEquals(404, $json->status);
        $this->assertNull($data);
    }

    public function testWriteActionSaveFailure(): void
    {
        $model = new MockModelWriteAction(false, true);

        $writeAction = new MockWriteAction($model);

        $responseBody = new MockResponseBodyWriteAction();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $response = $this->createMock(Response::class);

        $result = $writeAction($request, $response);

        $bodyStream = $result->getBody();
        $bodyStream->rewind();
        $contents = $bodyStream->getContents();
        $json = json_decode($contents, false);
        $data = $json->data;

        $this->assertFalse($json->success);
        $this->assertEquals(500, $json->status);
        $this->assertNull($data);
    }
}

class MockWriteAction extends WriteActionBase
{
    protected $model;

    public function __construct(ModelBase $model)
    {
        $this->model = $model;
    }
}

class MockResponseBodyWriteAction extends ResponseBody
{
    public function getParsedRequest(): array
    {
        return [
            'id' => 321,
            'created_at' => time(),
            'updated_at' => time(),
            'bogus' => 'garbage',
            'extra' => 'test',
            'protected' => 'blah'
        ];
    }
}

class MockModelWriteAction extends ModelBase
{
    protected $findFailure = false;
    protected $saveFailure = false;

    public const FIELDS = [
        'id' => '*integer',
        'test' => 'true',
        'protected' => '*bool',
        'extra' => 'string'
    ];

    public function __construct(bool $findFailure, bool $saveFailure = false)
    {
        $this->findFailure = $findFailure;
        $this->saveFailure = $saveFailure;
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTableName(): string
    {
        return 'mock_table';
    }

    public function find($id, $columns = ['*'])
    {
        if ($this->findFailure) {
            return null;
        }

        return $this;
    }

    public function save(array $options = [])
    {
        return !$this->saveFailure;
    }
}