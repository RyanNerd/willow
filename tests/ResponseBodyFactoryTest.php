<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\Route;
use Willow\Middleware\ResponseBody;
use Willow\Middleware\ResponseBodyFactory;

final class ResponseBodyFactoryTest extends TestCase
{
    public function testFactoryConstructor(): void
    {
        $responseBody = new MockResponseBody();

        $responseBodyFactory = new ResponseBodyFactory($responseBody);
        $this->assertInstanceOf(ResponseBodyFactory::class, $responseBodyFactory);

        $route = $this->createMock(Route::class);
        $route->expects($this->once())->method('getArgument')->with('id')->willReturn(null);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(null);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('response_body', $responseBody)
            ->willReturnSelf();
        $requestHandler = $this->createMock(RequestHandler::class);
        $response = $responseBodyFactory->__invoke($request, $requestHandler);
        $this->assertEquals(null, $response->getStatusCode());
    }
}

class MockResponseBody extends ResponseBody
{
    public function setParsedRequest(array $parsedRequest): ResponseBody
    {
        return $this;
    }
}