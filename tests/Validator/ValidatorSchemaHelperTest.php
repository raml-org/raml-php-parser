<?php

namespace Raml\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Raml\Body;
use Raml\Parser;
use Raml\Validator\ValidatorSchemaException;
use Raml\Validator\ValidatorSchemaHelper;

class ValidatorSchemaHelperTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * @param string $fixturePath
     * @return ValidatorSchemaHelper
     */
    public function getHelperForSchema($fixturePath)
    {
        $apiDefinition = $this->parser->parse($fixturePath);

        return new ValidatorSchemaHelper($apiDefinition);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfResourceNotFound()
    {
        $this->expectException(ValidatorSchemaException::class);
        $this->expectExceptionMessage('/images was not found');

        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/simple.raml');
        $helper->getResponse('get', '/images', 200);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfMethodNotFound()
    {
        $this->expectException(ValidatorSchemaException::class);
        $this->expectExceptionMessage('POST /songs was not found');

        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/simple.raml');
        $helper->getResponse('post', '/songs', 200);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfResponseNotFound()
    {
        $this->expectException(ValidatorSchemaException::class);
        $this->expectExceptionMessage('GET /songs with status code 300 was not found');

        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/simple.raml');
        $helper->getResponse('get', '/songs', 300);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfResponseBodyNotFound()
    {
        $this->expectException(ValidatorSchemaException::class);
        $this->expectExceptionMessage('GET /songs with content type application/xml was not found');

        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/simple.raml');
        $helper->getResponseBody('get', '/songs', 200, 'application/xml');
    }

    /**
     * @test
     */
    public function shouldCorrectlyReturnResponseBodyWithCompositeMediaType()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $actual = $helper->getRequestBody('post', '/songs', 'application/json;charset=UTF-8');
        $this->assertInstanceOf(Body::class, $actual);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfResponseBodyIsNotABodyObject()
    {
        $this->expectException(ValidatorSchemaException::class);
        $this->expectExceptionMessage('not a Body object');

        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/webFormBody.raml');
        $helper->getRequestBody('post', '/songs', 'application/x-www-form-urlencoded');
    }

    /**
     * @test
     */
    public function shouldGetRequiredOrAllQueryParameters()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/queryParameters.raml');

        $allParameters = $helper->getQueryParameters('get', '/songs');
        $requiredParameters = $helper->getQueryParameters('get', '/songs', true);

        $this->assertInternalType('array', $allParameters);
        $this->assertInternalType('array', $requiredParameters);

        $this->assertSame(['required_number', 'optional_long_string'], array_keys($allParameters));
        $this->assertSame(['required_number'], array_keys($requiredParameters));
    }

    /**
     * @test
     */
    public function shouldGetRequestBody()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $actual = $helper->getRequestBody('post', '/songs', 'application/json');

        $this->assertInstanceOf(Body::class, $actual);
        $this->assertSame('POST /songs JSON body', $actual->getDescription());
    }

    /**
     * @test
     */
    public function shouldGetResponse()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/requestBody.raml');
        $actual = $helper->getRequestBody('post', '/songs', 'application/json');

        $this->assertInstanceOf(Body::class, $actual);
        $this->assertSame('POST /songs JSON body', $actual->getDescription());
    }

    /**
     * @test
     */
    public function shouldGetResponseBody()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/responseBody.raml');
        $actual = $helper->getResponseBody('post', '/songs', 200, 'application/json');

        $this->assertInstanceOf(Body::class, $actual);
        $this->assertSame('POST /songs 200 response JSON body', $actual->getDescription());
    }

    /**
     * @test
     */
    public function shouldGetResponseHeaders()
    {
        $helper = $this->getHelperForSchema(__DIR__ . '/../fixture/validator/responseHeaders.raml');

        $allHeaders = $helper->getResponseHeaders('get', '/songs', 200);
        $requiredHeaders = $helper->getResponseHeaders('get', '/songs', 200, true);

        $this->assertInternalType('array', $allHeaders);
        $this->assertInternalType('array', $requiredHeaders);

        $this->assertSame(['X-Required-Header', 'X-Long-Optional-Header'], array_keys($allHeaders));
        $this->assertSame(['X-Required-Header'], array_keys($requiredHeaders));
    }
}
