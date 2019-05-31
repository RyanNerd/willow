<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as V;
use Slim\Psr7\Request;
use Willow\Controllers\ValidatorBase;
use Willow\Middleware\ResponseBody;

final class ValidatorTest extends TestCase
{
    public function testValidatorInvalid(): void
    {
        $validator = new MockValidator(false);

        $responseBody = new MockValidatorResponseBody();
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);
        $requestHandler = $this->createMock(RequestHandler::class);

        $result = $validator->__invoke($request, $requestHandler);
    }

    public function testValidatorValid(): void
    {
        $validator = new MockValidator(true);

        $responseBody = new MockValidatorResponseBody();
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('response_body')
            ->willReturn($responseBody);
        $requestHandler = $this->createMock(RequestHandler::class);

        $result = $validator->__invoke($request, $requestHandler);
    }
}

class MockValidator extends ValidatorBase
{
    protected $isValid = false;

    public function __construct(bool $isValid)
    {
        $this->isValid = $isValid;
    }

    public function processValidation(ResponseBody $responseBody, array &$parsedRequest): void
    {
        if (!V::key('extra')->validate($parsedRequest)) {
            $responseBody->registerParam('optional', 'extra', null);
        }

        if (!$this->isValid) {
            if (!V::primeNumber()->validate($parsedRequest['id'])) {
                $responseBody->registerParam('invalid', 'id', 'integer');
            }
        }
    }
}

class MockValidatorResponseBody extends ResponseBody
{
    public function getParsedRequest(): array
    {
        return [
            'id' => 22,
            'test' => true
        ];
    }
}