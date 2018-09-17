<?php

namespace Raml\Tests\NamedParameters;

use PHPUnit\Framework\TestCase;
use Raml\Exception\InvalidKeyException;
use Raml\NamedParameter;
use Raml\Parser;
use Raml\WebFormBody;

class WebFormBodyTest extends TestCase
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
     * @test
     */
    public function shouldThrowExceptionOnInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebFormBody('test');
    }

    /**
     * @test
     */
    public function shouldBeCreatedForValidMediaTypeUrlEncoded()
    {
        $raml = <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          string:
            type: string
            default: A string
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $this->assertInstanceOf(WebFormBody::class, $body);
    }

    /**
     * @test
     */
    public function shouldBeCreatedForValidMediaTypeFormData()
    {
        $raml = <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      multipart/form-data:
        formParameters:
          string:
            type: string
            default: A string
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('multipart/form-data');

        $this->assertInstanceOf(WebFormBody::class, $body);
    }

    /**
     * @test
     */
    public function shouldThrowErrorOnAttemptGetInvalidParameter()
    {
        $raml = <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      multipart/form-data:
        formParameters:
          string:
            type: string
            default: A string
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('multipart/form-data');

        $this->expectException(InvalidKeyException::class);

        try {
            $body->getParameter('badKey');
        } catch (InvalidKeyException $e) {
            $this->assertEquals('badKey', $e->getKey());

            throw $e;
        }
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetAllParameters()
    {
        $raml = <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      multipart/form-data:
        formParameters:
          string:
            type: string
            default: A string
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('multipart/form-data');

        $parameters = $body->getParameters();
        $expectedParameter = new NamedParameter('string');
        $expectedParameter->setDefault('A string');

        $this->assertEquals([
            'string' => $expectedParameter
        ], $parameters);
    }
}
