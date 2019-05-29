<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Controllers\Sample\SampleController;
use Willow\Controllers\Sample\SampleGetAction;
use Willow\Controllers\Sample\SampleGetValidator;
use Willow\Middleware\ResponseBody;

final class SampleTest extends TestCase
{
    public function testSampleController(): void
    {
        $sampleController = new SampleController();
        $group = $this->createMock(RouteCollectorProxyInterface::class);

        $group->expects($this->once())
            ->method('get')
            ->with('/sample/{id}');
        $sampleController->register($group);
    }

    public function testSampleGetAction(): void
    {
        $sampleGetAction = new SampleGetAction();

        $responseBody = new ResponseBody();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->willReturn($responseBody);
        $response = $this->createMock(Response::class);
        $id = uniqid('', false);
        $arg = ['id' => $id];

        $result = $sampleGetAction($request, $response, $arg);

        $body = $result->getBody();
        $body->rewind();
        $contents = $body->getContents();
        $json = json_decode($contents, false);

        $this->assertEquals(200, $json->status);
        $this->assertEquals($id, $json->data->id);
        $this->assertEquals('Sample test', $json->message);
    }

    public function testSampleGetValidator(): void
    {
        $sampleGetValidator = new SampleGetValidator();
        $id = uniqid('', false);
        $responseBody = new MockSampleRequestBody($id);

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $requestHandler = $this->createMock(RequestHandler::class);
        $result = $sampleGetValidator($request, $requestHandler);
    }

    public function testSampleGetValidatorFailure(): void
    {
        $sampleGetValidator = new SampleGetValidator();
        $id = 'IV';
        $responseBody = new MockSampleRequestBody($id);

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);

        $requestHandler = $this->createMock(RequestHandler::class);
        $result = $sampleGetValidator($request, $requestHandler);

        $body = $result->getBody();
        $body->rewind();
        $contents = $body->getContents();
        $json = json_decode($contents, false);

        $this->assertEquals(400, $json->status);
        $this->assertNull($json->data);
        $this->assertEquals('Roman numerals are not allowed.', $json->message);
    }
}

class MockSampleRequestBody extends ResponseBody
{
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getParsedRequest(): array
    {
        return [
            'id' => $this->id,
            'test' => true
        ];
    }
}