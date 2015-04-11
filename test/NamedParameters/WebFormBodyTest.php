<?php
namespace Raml\Test\NamedParameters;

class WebFormBodyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Raml\Parser
     */
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
    }

    // ---

    /** @test */
    public function shouldThrowExceptionOnInvalidType()
    {
        $this->setExpectedException('Exception', 'Invalid type');
        new \Raml\WebFormBody('test');
    }

    /** @test */
    public function shouldBeCreatedForValidMediaTypeUrlEncoded()
    {
        $raml =  <<<RAML
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

        $this->assertInstanceOf('\Raml\WebFormBody', $body);
    }

    /** @test */
    public function shouldBeCreatedForValidMediaTypeFormData()
    {
        $raml =  <<<RAML
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

        $this->assertInstanceOf('\Raml\WebFormBody', $body);
    }

    /** @test */
    public function shouldThrowErrorOnAttemptGetInvalidParameter()
    {
        $raml =  <<<RAML
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

        $this->setExpectedException('\Raml\Exception\InvalidKeyException', 'The key badKey does not exist.');

        try {
            $body->getParameter('badKey');
        } catch (\Raml\Exception\InvalidKeyException $e) {
            $this->assertEquals('badKey', $e->getKey());
            throw $e;
        }
    }

    /** @test */
    public function shouldBeAbleToGetAllParameters()
    {
        $raml =  <<<RAML
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
        $expectedParameter = new \Raml\NamedParameter('string');
        $expectedParameter->setDefault('A string');

        $this->assertEquals([
            'string' => $expectedParameter
        ], $parameters);
    }
}
