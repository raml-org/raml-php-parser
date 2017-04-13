<?php

use Raml\Validator\Validator;
use Raml\Validator\ValidatorSchemaHelper;

class ValidatorTest extends PHPUnit_Framework_TestCase
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
     * @return Validator
     */
    private function getValidatorForSchema($fixturePath)
    {
        $apiDefinition = $this->parser->parse($fixturePath);
        $helper = new ValidatorSchemaHelper($apiDefinition);

        return new Validator($helper);
    }

    /** @test */
    public function shouldCatchMissingParameters()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('');

        $this->setExpectedException(
            '\Raml\Validator\ValidatorRequestException',
            'required_number'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');
        $validator->validateRequest($this->request);
    }

    /** @test */
    public function shouldCatchInvalidParameters()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('required_number=5&optional_long_string=ABC');

        $this->setExpectedException(
            '\Raml\Validator\ValidatorRequestException',
            'optional_long_string'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');
        $validator->validateRequest($this->request);
    }

    /** @test */
    public function shouldCatchInvalidRequestBody()
    {
        $body = $this->getMock('\Psr\Http\Message\StreamInterface');
        $body->method('getContents')->willReturn('{"title":"Aaa"}');

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->request->method('getBody')->willReturn($body);
        
        $this->setExpectedException(
            '\Raml\Exception\InvalidSchemaException',
            'Invalid Schema.'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $validator->validateRequest($this->request);
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

    /** @test */
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
    public function shouldCatchInvalidResponseBody()
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
            '\Raml\Exception\InvalidSchemaException',
            'Invalid Schema.'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/responseBody.raml');
        $validator->validateResponse($this->request, $this->response);
    }
}
