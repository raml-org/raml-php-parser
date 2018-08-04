<?php

namespace Raml\Tests\Types;

use PHPUnit\Framework\TestCase;
use Raml\Parser;

class UnionTypeTest extends TestCase
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
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(201);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"id": 1, "name": "Sample name"}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateIncorrectType()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(201);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"id": 1, "name": false}', true));
        $this->assertFalse($type->isValid());
        $this->assertEquals(
            'name (Value did not pass validation against any type: '
                . 'integer (integer (Expected int, got (boolean) "")), '
                . 'string (string (Expected string, got (boolean) "")))',
            (string) $type->getErrors()[0]
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateNullableTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(203);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(json_decode('{"var": 10}', true));
        $this->assertTrue($type->isValid());
        $type->validate(json_decode('{"var": null}', true));
        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateNullableStringTypes()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(207);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(null);
        $this->assertTrue($type->isValid());

        $type->validate('');
        $this->assertTrue($type->isValid());

        $type->validate('string');
        $this->assertTrue($type->isValid());

        $type->validate(1);
        $this->assertFalse($type->isValid());
    }
}
