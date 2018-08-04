<?php

namespace Raml\Tests;

use PHPUnit\Framework\TestCase;
use Raml\Parser;

class TypeTest extends TestCase
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
    public function shouldCorrectlyValidateCorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title":"Good Song","artist":"An artist"}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateCorrectTypeMissingUnrequired()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title":"Good Song"}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateCorrectTypeMissingRequired()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"artist":"An artist"}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateIncorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([]);
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateAdditionalProperties()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"title": "Good Song", "duration":"3:09"}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateNullTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(204);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"var": null}', true));
        $this->assertTrue($type->isValid());

        $type->validate(json_decode('{"var": 10}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateRightDateTimeOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"datetimeOnly": "2017-12-07T15:50:48"}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateWrongDateTimeOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"datetimeOnly": "2017-12 15:50:48"}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateRightDateOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"dateOnly": "2016-02-28"}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateWrongDateOnlyTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(205);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"dateOnly": "2017-12-07T15:50:48"}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayIntegerRightTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"intArray": [1,2,3]}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayIntegerWrongTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"intArray": [1,2,"str"]}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayStringRightTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"strArray": ["one", "two"]}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayStringWrongTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"strArray": [1, "two"]}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayBooleanRightTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"boolArray": [true, false]}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayBooleanWrongTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"boolArray": [true, 0]}', true));
        $this->assertFalse($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayNumberRightTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"numberArray": [12, 13.5, 0]}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateArrayNumberWrongTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(206);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"numberArray": ["12", 0]}', true));
        $this->assertFalse($type->isValid());
    }
}
