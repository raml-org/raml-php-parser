<?php
namespace Raml\Test\NamedParameters;

class ParameterTypesTest extends \PHPUnit_Framework_TestCase
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

    /**
     * Returns a valid Body
     *
     * @throws \Exception
     *
     * @return \Raml\WebFormBody
     */
    private function getValidBody() {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    description: A post to do something
    body:
      application/x-www-form-urlencoded:
        formParameters:
          string:
            description: A string key
            type: string
            default: A string
          date:
            description: A date key
            type: date
            default: Sun, 06 Nov 1994 08:49:37 GMT
          integer:
            type: integer
            default: 10
          number:
            type: number
            default: 5.43
          boolean:
            type: boolean
            default: false
          file:
            type: file
RAML;


        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        return $body;
    }

    /** @test */
    public function shouldCorrectlyParseTypeDate()
    {
        $namedParameter = $this->getValidBody()->getParameter('date');

        $this->assertEquals('date', $namedParameter->getType());
        $this->assertInstanceOf('\DateTime', $namedParameter->getDefault());
        $this->assertEquals('1994-11-06 08:49:37', $namedParameter->getDefault()->format('Y-m-d h:i:s'));
    }

    /** @test */
    public function shouldCorrectlyParseTypeString()
    {
        $namedParameter = $this->getValidBody()->getParameter('string');

        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('A string', $namedParameter->getDefault() );
    }

    /** @test */
    public function shouldCorrectlyParseTypeInteger()
    {
        $namedParameter = $this->getValidBody()->getParameter('integer');

        $this->assertEquals('integer', $namedParameter->getType());
        $this->assertEquals(10, $namedParameter->getDefault());
    }

    /** @test */
    public function shouldCorrectlyParseTypeNumber()
    {
        $namedParameter = $this->getValidBody()->getParameter('number');

        $this->assertEquals('number', $namedParameter->getType());
        $this->assertEquals(5.43, $namedParameter->getDefault());
    }

    /** @test */
    public function shouldCorrectlyParseTypeBoolean()
    {
        $namedParameter = $this->getValidBody()->getParameter('boolean');

        $this->assertEquals('boolean', $namedParameter->getType());
        $this->assertFalse($namedParameter->getDefault());
    }

    /** @test */
    public function shouldCorrectlyParseTypeFile()
    {
        $namedParameter = $this->getValidBody()->getParameter('file');

        $this->assertEquals('file', $namedParameter->getType());
        $this->assertNull($namedParameter->getDefault());
    }

    // ---

    /** @test */
    public function shouldThrowExceptionOnInvalidString()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            default: 10
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a string');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidNumber()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: number
            default: string
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a number');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidInteger()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: integer
            default: 10.1
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not an integer');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidDate()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: date
            default: 2000
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a dateTime object');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidBoolean()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: boolean
            default: 1
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a boolean');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidFile()
    {        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: file
            default: 1
RAML;


        $this->setExpectedException('Exception', 'A default value cannot be set for a file');
        $this->parser->parseFromString($raml, '');
    }
}
