<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Willow\Middleware\ResponseBody;

final class ResponseBodyTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        ini_set('zend.assertions', '1');
        ini_set('assert.exception', '1');
        parent::__construct($name, $data, $dataName);
    }

    public function testGetSetParsedBody(): void
    {
        $responseBody = new ResponseBody();
        $testArray = ['test' => true];
        $responseBody = $responseBody->setParsedRequest($testArray);
        $testResult = $responseBody->getParsedRequest();
        $this->assertEquals($testArray, $testResult);
    }

    public function testGetParsedBodyFailure(): void
    {
        $this->expectException('TypeError');
        $responseBody = new ResponseBody();
        $testResult = $responseBody->getParsedRequest();
    }

    public function testGetSetIsAdmin(): void
    {
        $responseBody = new ResponseBody();
        $testResult = $responseBody->getIsAdmin();
        $this->assertFalse($testResult);
        $responseBody = $responseBody->setIsAdmin();
        $testResult = $responseBody->getIsAdmin();
        $this->assertTrue($testResult);
    }

    public function testGetSetIsAuthenticated(): void
    {
        $responseBody = new ResponseBody();
        $testResult = $responseBody->getIsAuthenticated();
        $this->assertFalse($testResult);
        $responseBody = $responseBody->setIsAuthenticated();
        $testResult = $responseBody->getIsAuthenticated();
        $this->assertTrue($testResult);
    }

    public function testHasMissingRequiredOrInvalid(): void
    {
        $responseBody = new ResponseBody();
        $resultTest = $responseBody->hasMissingRequiredOrInvalid();
        $this->assertFalse($resultTest);
    }

    public function testRegisterParam1(): void
    {
        $responseBody = new ResponseBody();
        $responseBody->registerParam('optional', 'test', 'string');
        $responseResult = $responseBody();
        $bodyStream = $responseResult->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $responseTest = json_decode($body, true);
        $responseExpected = [
            'authenticated' => false,
            'success' => true,
            'status' => 200,
            'data' => null,
            'missing' => ['optional' => ['test' => 'string']],
            'message' => '',
            'timestamp' => $responseTest['timestamp']
        ];
        $this->assertEquals($responseExpected, $responseTest);
    }

    public function testRegisterParam2(): void
    {
        $responseBody = new ResponseBody();
        $responseBody->registerParam('optional', 'test', null);
        $responseResult = $responseBody();
        $bodyStream = $responseResult->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $responseTest = json_decode($body, true);
        $responseExpected = [
            'authenticated' => false,
            'success' => true,
            'status' => 200,
            'data' => null,
            'missing' => ['optional' => ['test' => 'unknown']],
            'message' => '',
            'timestamp' => $responseTest['timestamp']
        ];
        $this->assertEquals($responseExpected, $responseTest);
    }

    public function testRegisterParamAssertFailure1(): void
    {
        $this->expectExceptionMessage('assert(in_array($section, [\'optional\', \'required\', \'invalid\']))');
        $responseBody = new ResponseBody();
        $responseBody->registerParam('bogus', 'test', null);
    }

    public function testRegisterParamAssertFailure2(): void
    {
        $this->expectExceptionMessage('$name !== \'\'');
        $responseBody = new ResponseBody();
        $responseBody->registerParam('required', '', null);
    }

    public function testRegisterParams(): void
    {
        $responseBody = new ResponseBody();
        $responseBody->registerParams('required', ['test1', 'test2'], 'string');
        $responseResult = $responseBody();
        $bodyStream = $responseResult->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $responseTest = json_decode($body, true);
        $responseExpected = [
            'authenticated' => false,
            'success' => true,
            'status' => 200,
            'data' => null,
            'missing' => [
                'required' => [
                    'test1' => 'string',
                    'test2' => 'string'
                ]
            ],
            'message' => '',
            'timestamp' => $responseTest['timestamp']
        ];
        $this->assertEquals($responseExpected, $responseTest);
    }

    public function testSetData(): void
    {
        $responseBody = new ResponseBody();
        $responseBody = $responseBody->setData(['test' => true]);
        $responseResult = $responseBody();
        $bodyStream = $responseResult->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $responseTest = json_decode($body, true);
        $responseExpected = [
            'authenticated' => false,
            'success' => true,
            'status' => 200,
            'data' => ['test' => true],
            'missing' => [],
            'message' => '',
            'timestamp' => $responseTest['timestamp']
        ];
        $this->assertEquals($responseExpected, $responseTest);
    }

    public function testSetGetStatus(): void
    {
        $responseBody = new ResponseBody();
        $responseBody = $responseBody->setStatus(418);
        $responseResult = $responseBody->getStatus();
        $this->assertEquals(418, $responseResult);
    }

    public function testGetStatusDefault(): void
    {
        $responseBody = new ResponseBody();
        $responseResult = $responseBody->getStatus();
        $this->assertEquals(200, $responseResult);
    }

    public function testSetMessage(): void
    {
        $responseBody = new ResponseBody();
        $responseBody = $responseBody->setMessage('testing');
        $responseResult = $responseBody();
        $bodyStream = $responseResult->getBody();
        $bodyStream->rewind();
        $body = $bodyStream->getContents();
        $responseTest = json_decode($body, true);
        $responseExpected = [
            'authenticated' => false,
            'success' => true,
            'status' => 200,
            'data' => null,
            'missing' => [],
            'message' => 'testing',
            'timestamp' => $responseTest['timestamp']
        ];
        $this->assertEquals($responseExpected, $responseTest);
    }

    public function testSetMessageAssertFailure(): void
    {
        $this->expectExceptionMessage('assert($message !== \'\')');
        $responseBody = new ResponseBody();
        $responseBody->setMessage('');
    }
}