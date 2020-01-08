<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Controllers\Sample\SampleController;
use Willow\Controllers\Sample\SampleGetAction;
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
}
