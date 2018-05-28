<?php

class ArrayTest extends PHPUnit_Framework_TestCase
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
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(
            [
                ['id' => 1, 'name' => 'Sample 1']
            ]
        );

        self::assertTrue($type->isValid());
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectArraySizeLessThanMinimum()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([]);

        self::assertFalse($type->isValid());
        self::assertEquals('Sample[] (Allowed array size: between 1 and 2, got 0)', (string) $type->getErrors()[0]);
    }

    /** @test */
    public function shouldCorrectlyParseTypeFacet()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(208);
        /** @var \Raml\Body $body */
        $body = $response->getBodyByType('application/json');
        /** @var \Raml\Types\ArrayType $type */
        $type = $body->getType();

        $this->assertSame('Sample', $type->getItems()->getName());
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectTypeWhenArraySizeExceedsMaximum()
    {
        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(
            [
                ['id' => 1, 'name' => 'Sample 1'],
                ['id' => 2, 'name' => 'Sample 2'],
                ['id' => 3, 'name' => 'Sample 3']
            ]
        );

        self::assertFalse($type->isValid());
        self::assertEquals('Sample[] (Allowed array size: between 1 and 2, got 3)', (string) $type->getErrors()[0]);
    }
}
