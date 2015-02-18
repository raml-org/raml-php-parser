<?php

class ApiDefinitionTest extends PHPUnit_Framework_TestCase
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
    public function shouldReturnFullResourcesForRamlFileWithDefaultFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');
        $routes = $api->getResourcesAsUri();

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
                'GET /songs',
                'POST /songs/{songId}',
                'GET /songs/{songId}',
                'DELETE /songs/{songId}'
            ], array_keys($routes->getRoutes()));
    }

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithNoFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $noFormatter = new Raml\RouteFormatter\NoRouteFormatter();
        $routes = $api->getResourcesAsUri($noFormatter, $api->getResources());

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
            'GET /songs',
            'POST /songs/{songId}',
            'GET /songs/{songId}',
            'DELETE /songs/{songId}'
        ], array_keys($routes->getRoutes()));
    }

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithSymfonyFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $routeCollection= new Symfony\Component\Routing\RouteCollection();
        $routeFormatter = new Raml\RouteFormatter\SymfonyRouteFormatter($routeCollection);
        $routes = $api->getResourcesAsUri($routeFormatter, $api->getResources());

        $this->assertEquals($routeFormatter, $routes);

        $this->assertCount(4, $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\RouteCollection', $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $routeFormatter->getRoutes()->get('GET /songs/'));
        $this->assertEquals(['http'], $routeFormatter->getRoutes()->get('GET /songs/')->getSchemes());
    }

    /** @test */
    public function shouldReturnURIProtocol()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/protocols/noProtocolSpecified.raml');
        $this->assertCount(1, $api->getProtocols());
        $this->assertSame(array(
            'HTTP',
        ), $api->getProtocols());
    }

    /** @test */
    public function shouldReturnProtocolsIfSpecified()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/protocols/protocolsSpecified.raml');
        $this->assertCount(2, $api->getProtocols());
        $this->assertSame(array(
            'HTTP',
            'HTTPS'
        ), $api->getProtocols());
    }

    /** @test */
    public function shouldThrowInvalidProtocolExceptionIfWrongProtocol()
    {
        $this->setExpectedException('Raml\Exception\BadParameter\InvalidProtocolException');

        $this->parser->parse(__DIR__.'/fixture/protocols/invalidProtocolsSpecified.raml');
    }
}
