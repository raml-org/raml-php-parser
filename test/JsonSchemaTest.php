<?php

class JsonSchemaTest extends PHPUnit_Framework_TestCase
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

    /** @test */
    public function shouldReturnJsonString()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $schemaString = (string) $schema;
        $this->assertInternalType('string', $schemaString);
        $this->assertEquals('A list of songs', json_decode($schemaString)->description);
    }

    /** @test */
    public function shouldCorrectlyValidateCorrectJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertTrue($schema->validate('[{"title":"Good Song","artist":"An artist"}]'));
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectJson()
    {
        $this->setExpectedException('\Raml\Exception\InvalidSchemaException');

        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $schema->validate('{}');
    }

    /** @test */
    public function shouldCorrectlyValidateInvalidJson()
    {
        $this->setExpectedException('\Raml\Exception\InvalidJsonException');
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $schema->validate('{');
    }
}
