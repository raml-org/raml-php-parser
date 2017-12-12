<?php

class TypeTest extends PHPUnit_Framework_TestCase
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
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title":"Good Song","artist":"An artist"}', true));
        self::assertTrue($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateCorrectTypeMissingUnrequired()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title":"Good Song"}', true));
        self::assertTrue($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateCorrectTypeMissingRequired()
    {

        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"artist":"An artist"}', true));
        self::assertFalse($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([]);
        self::assertFalse($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateAdditionalProperties()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title": "Good Song", "duration":"3:09"}', true));
        self::assertFalse($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateNullTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(204);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"var": null}', true));
        self::assertTrue($type->isValid());

        $type->validate(json_decode('{"var": 10}', true));
        self::assertFalse($type->isValid());

    }

    /** @test */
    public function shouldCorrectlyValidateDateTimeOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"datetimeOnly": "2017-12-07T15:50:48"}', true));
        self::assertTrue($type->isValid());

        $type->validate(json_decode('{"datetimeOnly": "2017-12 15:50:48"}', true));
        self::assertFalse($type->isValid());

    }

    /** @test */
    public function shouldCorrectlyValidateDateOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"dateOnly": "2016-02-28"}', true));
        self::assertTrue($type->isValid());

        $type->validate(json_decode('{"dateOnly": "2017-12-07T15:50:48"}', true));
        self::assertFalse($type->isValid());

    }
}
