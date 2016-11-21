<?php

use Raml\Validator\ResponseValidator;
use Raml\Validator\ValidatorSchemaHelper;

class ResponseValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Raml\Parser
     */
    private $parser;
    /**
     * @var \Psr\Http\Message\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;
    /**
     * @var \Psr\Http\Message\ResponseInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $response;
    /**
     * @var \Psr\Http\Message\UriInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $uri;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
        $this->uri = $this->getMock('\Psr\Http\Message\UriInterface');
        $this->request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $this->request->method('getUri')->willReturn($this->uri);

        $this->response = $this->getMock('\Psr\Http\Message\ResponseInterface');
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

    /** @test */
    public function shouldCatchMissingHeaders()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeaders')->willReturn([]);

        $this->setExpectedException(
            '\Raml\Validator\ValidatorResponseException',
            'X-Required-Header'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    /** @test */
    public function shouldCatchInvalidHeaders()
    {
        $headers = [
            'X-Required-Header'      => ['123456'],
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

        $this->setExpectedException(
            '\Raml\Validator\ValidatorResponseException',
            'X-Long-Optional-Header'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    public function shouldPassOnEmptyBodyIfNotRequired()
    {
        $json = '';

        $headers = [
            'X-Required-Header'      => ['123456'],
            'X-Long-Optional-Header' => ['Abcdefghijkl'],
        ];

        $map = [
            ['X-Required-Header', [['123456']]],
            ['X-Long-Optional-Header', [['Abcdefg', 'Abc']]],
        ];

        $body = $this->getMock('\Psr\Http\Message\StreamInterface');
        $body->method('getContents')->willReturn($json);

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeader')->willReturnMap($map);
        $this->response->method('getHeaders')->willReturn($headers);
        $this->response->method('getBody')->willReturn($body);

        $this->setExpectedException(
            '\Raml\Validator\ValidatorResponseException',
            'X-Long-Optional-Header'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');
        $validator->validateResponse($this->request, $this->response);
    }

    /** @test */
    public function shouldCatchInvalidBody()
    {
        $json = '{}';

        $headers = [
            'X-Required-Header'      => ['123456'],
            'X-Long-Optional-Header' => ['Abcdefghijkl'],
        ];

        $map = [
            ['X-Required-Header', [['123456']]],
            ['X-Long-Optional-Header', [['Abcdefg', 'Abc']]],
        ];

        $body = $this->getMock('\Psr\Http\Message\StreamInterface');
        $body->method('getContents')->willReturn($json);

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->method('getHeader')->willReturnMap($map);
        $this->response->method('getHeaders')->willReturn($headers);
        $this->response->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->response->method('getBody')->willReturn($body);

        $this->setExpectedException(
            '\Raml\Validator\ValidatorResponseException',
            'title (required), artist (required)'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseBody.raml');
        $validator->validateResponse($this->request, $this->response);
    }
}
