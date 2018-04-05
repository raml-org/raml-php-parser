<?php

use Raml\Validator\RequestValidator;
use Raml\Validator\ValidatorSchemaHelper;

class RequestValidatorTest extends PHPUnit_Framework_TestCase
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
    }

    /**
     * @param string $fixturePath
     * @return RequestValidator
     */
    private function getValidatorForSchema($fixturePath)
    {
        $apiDefinition = $this->parser->parse($fixturePath);
        $helper = new ValidatorSchemaHelper($apiDefinition);

        return new RequestValidator($helper);
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
    public function shouldCatchInvalidBody()
    {
        $body = $this->getMock('\Psr\Http\Message\StreamInterface');
        $body->method('getContents')->willReturn('{"title":"Aaa"}');

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->request->method('getBody')->willReturn($body);
        
        $this->setExpectedException(
            '\Raml\Validator\ValidatorRequestException',
            'title (minLength), artist (required)'
        );

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $validator->validateRequest($this->request);
    }
}
