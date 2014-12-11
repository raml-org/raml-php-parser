<?php
namespace Raml\Test\NamedParameters;

class MutlipleTypesTest extends \PHPUnit_Framework_TestCase
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
    public function shouldGetTheResourceOnTheBaseUrl()
    {
        $apiDefinition = $this->parser->parse(__DIR__ . '/fixture/multipleTypes.raml');

        $resource = $apiDefinition->getResourceByUri('/');
        $this->assertInstanceOf('Raml\Resource', $resource);
    }

    /** @test */
    public function shouldReturnAnArrayOfTypes()
    {
        $apiDefinition = $this->parser->parse(__DIR__ . '/fixture/multipleTypes.raml');

        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $this->assertInstanceOf('\Raml\WebFormBody', $body);
    }
}
