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
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

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
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $schema->validate(json_decode('[{"title":"Good Song","artist":"An artist"}]'));
        $this->assertTrue($schema->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $schema->validate('{}');
        $this->assertFalse($schema->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateInvalidJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $schema->validate('{');
        $this->assertFalse($schema->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateJsonAsArray()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId}');
        $method = $resource->getMethod('post');
        $request = $method->getBodyByType('application/json');
        /** @var \Raml\Schema\Definition\JsonSchemaDefinition $schema */
        $schema = $request->getSchema();

        $schema->validate(json_decode('{"title":"Title", "artist": "Artist"}', true));
        $this->assertTrue($schema->isValid());
    }
}
