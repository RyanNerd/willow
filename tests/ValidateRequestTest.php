<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;
use Willow\Middleware\ResponseBody;
use Willow\Middleware\ValidateRequest;

final class ValidateRequestTest extends TestCase
{
    public function testValidateRequest(): void
    {
        $validateRequest = new ValidateRequest();
        $responseBody = new ResponseBody();

        $request = $this->createMock(Request::class);
        $request->expects($this->atLeastOnce())->method('getAttribute')->with('response_body')->willReturn($responseBody);
        $request->expects($this->once())->method('withAttribute')->with('response_body')->willReturnSelf();

        $requestHandler = $this->createMock(RequestHandler::class);

        $result = $validateRequest($request, $requestHandler);
    }
}



