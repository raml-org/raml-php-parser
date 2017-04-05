<?php

class UnionTypeTest extends PHPUnit_Framework_TestCase
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
    public function shouldCorrectlyValidateCorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(201);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"id": 1, "name": "Sample name"}', true));
        self::assertTrue($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(201);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"id": 1, "name": false}', true));
        self::assertFalse($type->isValid());
        self::assertEquals(
            'name (Value did not pass validation against any type: '
                . 'integer (integer (Expected int, got (boolean) "")), '
                . 'string (string (Expected string, got (boolean) "")))',
            (string) $type->getErrors()[0]
        );
    }
}
