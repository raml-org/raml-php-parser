<?php

namespace Raml\Tests\Types;

use PHPUnit\Framework\TestCase;
use Raml\Body;
use Raml\Parser;
use Raml\Types\ArrayType;

class ArrayTest extends TestCase
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
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([
            ['id' => 1, 'name' => 'Sample 1']
        ]);

        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateIncorrectArraySizeLessThanMinimum()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([]);

        $this->assertFalse($type->isValid());
        $this->assertEquals('Sample[] (Allowed array size: between 1 and 2, got 0)', (string) $type->getErrors()[0]);
    }

    /**
     * @test
     */
    public function shouldCorrectlyParseTypeFacet()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(208);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        /** @var ArrayType $type */
        $type = $body->getType();

        $this->assertSame('Sample', $type->getItems()->getName());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateIncorrectTypeWhenArraySizeExceedsMaximum()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([
            ['id' => 1, 'name' => 'Sample 1'],
            ['id' => 2, 'name' => 'Sample 2'],
            ['id' => 3, 'name' => 'Sample 3']
        ]);

        $this->assertFalse($type->isValid());
        $this->assertEquals('Sample[] (Allowed array size: between 1 and 2, got 3)', (string) $type->getErrors()[0]);
    }
}
