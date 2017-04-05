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
    public function shouldCorrectlyValidateIncorrectType()
    {
        $this->markTestSkipped();

        $simpleRaml = $this->parser->parse(__DIR__ . '/../fixture/simple_types.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(202);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate(
            [
                ['id' => 1, 'name' => 'Sample 1'],
                ['id' => 2, 'name' => 'Sample 2']
            ]
        );

        self::assertFalse($type->isValid());
    }
}
