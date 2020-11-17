<?php

namespace Raml\Tests\Validator;

use Negotiation\Negotiator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Raml\Body;
use Raml\Parser;
use Raml\Validator\RequestValidator;
use Raml\Validator\ValidatorRequestException;
use Raml\Validator\ValidatorSchemaHelper;
use Raml\ValidatorInterface;

class RequestValidatorTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var RequestInterface&MockObject
     */
    private $request;

    /**
     * @var UriInterface&MockObject
     */
    private $uri;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
        $this->uri = $this->createMock(UriInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->request->method('getUri')->willReturn($this->uri);
    }

    /**
     * @test
     */
    public function shouldCatchWrongMediaType()
    {
        $this->expectException(ValidatorRequestException::class);
        $this->expectExceptionMessage('Invalid Media type');

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('');
        $this->request->method('getHeaderLine')->with('Accept')->willReturn('application/xml');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestAcceptHeader.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function shouldSuccessfullyAssertWildcardAcceptHeader()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('');
        $this->request->method('getHeaderLine')->with('Accept')->willReturn('application/json');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestAcceptHeader.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function shouldNotAssertBodyOnGetRequest()
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('');
        $this->request->method('getHeaderLine')->with('Accept')->willReturn('application/json');

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('required_number=5');
        $this->request->method('getBody')->willReturn($body);

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     */
    public function shouldCatchMissingParameters()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('');
        $this->request->method('getHeaderLine')->with('Accept')->willReturn('application/json');

        $this->expectException(ValidatorRequestException::class);
        $this->expectExceptionMessage('required_number');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     */
    public function shouldCatchInvalidParameters()
    {
        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->uri->method('getQuery')->willReturn('required_number=5&optional_long_string=ABC');
        $this->request->method('getHeaderLine')->with('Accept')->willReturn('application/json');

        $this->expectException(ValidatorRequestException::class);
        $this->expectExceptionMessage('optional_long_string');

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     */
    public function shouldCatchInvalidBody()
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('{"title":"Aaa"}');

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->request->method('getHeaderLine')->willReturn('application/json');
        $this->request->method('getBody')->willReturn($body);

        $this->expectException(ValidatorRequestException::class);

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function shouldAllowEmptyRequestBody()
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('');

        $this->request->method('getMethod')->willReturn('get');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->request->method('getBody')->willReturn($body);

        $validator = $this->getValidatorForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $validator->validateRequest($this->request);
    }

    /**
     * @test
     */
    public function shouldParseContentTypeHeader()
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('{"title":"Aaa"}');

        $this->request->method('getMethod')->willReturn('post');
        $this->uri->method('getPath')->willReturn('/songs');
        $this->request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json; charset=us-ascii');
        $this->request->method('getBody')->willReturn($body);

        $schemaBody = $this->createMock(Body::class);
        $schemaBody
            ->expects($this->atLeastOnce())
            ->method('getValidator')
            ->willReturn($this->createMock(ValidatorInterface::class));

        $apiDefinition = $this->parser->parse(__DIR__ . '/../fixture/validator/requestBody.raml');
        $schemaHelper = $this->getMockBuilder(ValidatorSchemaHelper::class)
            ->setConstructorArgs([$apiDefinition])
            ->setMethods(['getRequestBody'])
            ->getMock();
        $schemaHelper
            ->expects($this->once())
            ->method('getRequestBody')
            ->with('post', '/songs', 'application/json')
            ->willReturn($schemaBody);

        $validator = new RequestValidator($schemaHelper, new Negotiator());
        $validator->validateRequest($this->request);
    }

    /**
     * @param string $fixturePath
     * @return RequestValidator
     */
    private function getValidatorForSchema($fixturePath)
    {
        $apiDefinition = $this->parser->parse($fixturePath);
        $helper = new ValidatorSchemaHelper($apiDefinition);

        return new RequestValidator($helper, new Negotiator());
    }
}
