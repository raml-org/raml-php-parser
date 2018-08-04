<?php

namespace Raml\Tests\NamedParameters;

use PHPUnit\Framework\TestCase;
use Raml\Parser;
use Raml\Resource;
use Raml\WebFormBody;

class MultipleTypesTest extends TestCase
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
    public function shouldGetTheResourceOnTheBaseUrl()
    {
        $apiDefinition = $this->parser->parse(__DIR__ . '/fixture/multipleTypes.raml');

        $resource = $apiDefinition->getResourceByUri('/');
        $this->assertInstanceOf(Resource::class, $resource);
    }

    /**
     * @test
     */
    public function shouldReturnAnArrayOfTypes()
    {
        $apiDefinition = $this->parser->parse(__DIR__ . '/fixture/multipleTypes.raml');

        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $this->assertInstanceOf(WebFormBody::class, $body);
    }
}
