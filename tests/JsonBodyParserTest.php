<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Willow\Middleware\JsonBodyParser;

final class JsonBodyParserTest extends TestCase
{
    public function testSanity()
    {
        $this->assertTrue(true);
    }

    /**
     *  JsonBodyParser should reject any Content-Type for POST other than application/json
     */
    public function testInvalidContentTypePost(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('getHeaderLine')->willReturn('text/bogus');
        $request->expects($this->once())->method('getMethod')->willReturn('POST');
        $requestHandler = $this->createMock(RequestHandler::class);
        $jsonBodyParser = new JsonBodyParser();
        $response = $jsonBodyParser->process($request, $requestHandler);

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $responseBody = $bodyStream->getContents();
        $responseJSON = json_decode($responseBody, true);

        $this->assertFalse($responseJSON['authenticated']);
        $this->assertFalse($responseJSON['success']);
        $this->assertEquals(400, $responseJSON['status']);
        $this->assertNull($responseJSON['data']);
        $this->assertEquals('Invalid Content-Type: text/bogus', $responseJSON['message']);
        $this->assertIsNumeric($responseJSON['timestamp']);
    }

    /**
     *  JsonBodyParser should reject any Content-Type for PATCH other than application/json
     */
    public function testInvalidContentTypePatch(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('getHeaderLine')->willReturn('text/bogus');
        $request->expects($this->once())->method('getMethod')->willReturn('PATCH');
        $requestHandler = $this->createMock(RequestHandler::class);
        $jsonBodyParser = new JsonBodyParser();
        $response = $jsonBodyParser->process($request, $requestHandler);

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $responseBody = $bodyStream->getContents();
        $responseJSON = json_decode($responseBody, true);

        $this->assertFalse($responseJSON['authenticated']);
        $this->assertFalse($responseJSON['success']);
        $this->assertEquals(400, $responseJSON['status']);
        $this->assertNull($responseJSON['data']);
        $this->assertEquals('Invalid Content-Type: text/bogus', $responseJSON['message']);
        $this->assertIsNumeric($responseJSON['timestamp']);
    }

    public function testContentTypeInvalidJson(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('getHeaderLine')->willReturn('application/json');
        $requestHandler = $this->createMock(RequestHandler::class);
        $jsonBodyParser = new JsonBodyParser();
        $response = $jsonBodyParser->process($request, $requestHandler);

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $responseBody = $bodyStream->getContents();
        $responseJSON = json_decode($responseBody, true);

        $this->assertFalse($responseJSON['authenticated']);
        $this->assertFalse($responseJSON['success']);
        $this->assertEquals(400, $responseJSON['status']);
        $this->assertNull($responseJSON['data']);
        $this->assertEquals('Invalid JSON', $responseJSON['message']);
        $this->assertIsNumeric($responseJSON['timestamp']);
    }

}
