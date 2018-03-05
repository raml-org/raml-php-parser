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

    /** @test */
    public function shouldCorrectlyValidateNullableTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(203);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"var": 10}', true));
        self::assertTrue($type->isValid());
        $type->validate(json_decode('{"var": null}', true));
        self::assertTrue($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateNullableStringTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(207);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(null);
        self::assertTrue($type->isValid());

        $type->validate("");
        self::assertTrue($type->isValid());

        $type->validate("string");
        self::assertTrue($type->isValid());

        $type->validate(1);
        self::assertFalse($type->isValid());
    }
}
