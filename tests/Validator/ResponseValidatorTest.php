<?php

namespace Raml\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Raml\Parser;
use Raml\Validator\ResponseValidator;
use Raml\Validator\ValidatorResponseException;
use Raml\Validator\ValidatorSchemaHelper;

class ResponseValidatorTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;
    /**
     * @var ResponseInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $response;
    /**
     * @var UriInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $uri;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
        $this->uri = $this->createMock(UriInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->request->method('getUri')->willReturn($this->uri);

        $this->response = $this->createMock(ResponseInterface::class);
    }

    /**
     * @param string $fixturePath
     * @return ResponseValidator
     */
    private function getValidatorForSchema($fixturePath)
    {
        $apiDefinition = $this->parser->parse($fixturePath);
        $helper = new ValidatorSchemaHelper($apiDefinition);

        return new ResponseValidator($helper);
    }

    /**
     * @test
     */
    public function shouldCatchMissingHeaders()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeaders')->willReturn([]);

        $this->expectException(ValidatorResponseException::class);
        $this->expectExceptionMessage('X-Required-Header');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    /**
     * @test
     */
    public function shouldCatchInvalidHeaders()
    {
        $headers = [
            'X-Required-Header' => ['123456'],
            'X-Long-Optional-Header' => ['Abcdefg', 'Abc'],
        ];

        $map = [
            ['X-Required-Header', [['123456']]],
            ['X-Long-Optional-Header', [['Abcdefg', 'Abc']]],
        ];

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeader')->willReturnMap($map);
        $this->response->method('getHeaders')->willReturn($headers);

        $this->expectException(ValidatorResponseException::class);
        $this->expectExceptionMessage('X-Long-Optional-Header');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    /**
     * @test
     */
    public function shouldPassOnEmptyBodyIfNotRequired()
    {
        $json = '';

        $headers = [
            'X-Required-Header' => ['123456'],
            'X-Long-Optional-Header' => ['Abcdefghijkl'],
        ];

        $map = [
            ['X-Required-Header', [['123456']]],
            ['X-Long-Optional-Header', [['Abcdefg', 'Abc']]],
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn($json);

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeader')->willReturnMap($map);
        $this->response->method('getHeaders')->willReturn($headers);
        $this->response->method('getBody')->willReturn($body);

        $this->expectException(ValidatorResponseException::class);
        $this->expectExceptionMessage('X-Long-Optional-Header');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    /**
     * @test
     */
    public function shouldCatchInvalidBody()
    {
        $json = '{}';

        $headers = [
            'X-Required-Header' => ['123456'],
            'X-Long-Optional-Header' => ['Abcdefghijkl'],
        ];

        $map = [
            ['X-Required-Header', [['123456']]],
            ['X-Long-Optional-Header', [['Abcdefg', 'Abc']]],
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn($json);

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeader')->willReturnMap($map);
        $this->response->method('getHeaders')->willReturn($headers);
        $this->response->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->response->method('getBody')->willReturn($body);

        $this->expectException(ValidatorResponseException::class);

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseBody.raml');
        $validator->validateResponse($this->request, $this->response);
    }
}
